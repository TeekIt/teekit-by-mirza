<?php

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\QtyController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Api\v1\DriverController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PromoCodesController;
use App\Http\Controllers\RattingsController;
use App\Http\Controllers\ReferralCodeRelationController;
use App\Http\Controllers\WithdrawalRequestsController;
use App\Services\StripeServices;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', fn() =>  'Teek it API Routes Are Working Fine 😃');
/*
|--------------------------------------------------------------------------
| Authentication API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register', 'registerBuyer');
    Route::post('register_google', 'registerBuyerFromGoogle');
    Route::post('login', 'loginBuyer');
    Route::post('login_google', 'loginBuyerFromGoogle');
    Route::get('verify', 'verify');
    Route::post('change-password', 'changePassword');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::post('update', 'updateUser');
    Route::post('updateStatus', 'updateStatus');
    Route::get('delivery_boys', 'deliveryBoys');
    Route::get('get_user/{user_id}', 'getUserDetails');
    Route::post('user/delete', 'deleteUser');
    Route::get('me', 'me');
});
/*
|--------------------------------------------------------------------------
| Registration, confirmations and verification
|--------------------------------------------------------------------------
*/
Route::post('password/email', [ForgotPasswordController::class, 'getResetToken']);
Route::post('password/reset', [ResetPasswordController::class, 'reset']);
/*
|--------------------------------------------------------------------------
| Qty API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('qty')->controller(QtyController::class)->group(function () {
    Route::get('product/{store_id}/{prod_id}', 'getById');
    Route::post('update/{prod_id}', 'updateById');
    // Route::post('insert_parent_qty_to_child', 'insertParentQtyToChild')->middleware('jwt.verify');
    // Route::get('multi-curl', 'QtyController@multiCURL');
});
/*
|--------------------------------------------------------------------------
| Category API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('category')->controller(CategoriesController::class)->group(function () {
    Route::post('add', 'add');
    Route::post('update/{product_id}', 'update');
    Route::get('{categoryId}/products', 'productsByCategory');
    Route::get('get-stores-by-category', 'stores');
    Route::get('all', 'all');
});
/*
|--------------------------------------------------------------------------
| Seller API Routes Without JWT Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('sellers')->controller(UsersController::class)->group(function () {
    Route::get('/', 'sellers');
    Route::get('{seller_id}/{product_name}', 'searchSellerProducts');
});
/*
|--------------------------------------------------------------------------
| Notifications API Routes Without JWT Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('notifications')->controller(NotificationsController::class)->group(function () {
    Route::post('save_token', 'saveToken');
});
/*
|--------------------------------------------------------------------------
| API Routes With JWT Authentication
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.verify'])->group(function () {
    Route::prefix('product')->group(function () {
        Route::controller(ProductsController::class)->group(function () {
            Route::post('add', 'add');
            Route::post('add/bulk', 'importProductsAPI');
            Route::post('update/{product_id}', 'update');
            Route::post('update_price_qty/bulk', 'updatePriceAndQtyBulk');
            Route::get('delete/{product_id}', 'delete');
            Route::get('delete_image/{image_id}/{product_id}', 'deleteImage');

            Route::withoutMiddleware(['jwt.verify'])->group(function () {
                Route::get('all', 'all');
                Route::post('search', 'search');
                Route::get('view', 'view');
                Route::post('view/bulk', 'bulkView');
                Route::get('seller', 'sellerProducts');
                Route::get('sortbyprice', 'sortByPrice');
                Route::get('sortByLocation', 'sortByLocation');
                Route::post('recheck_products', 'recheckProducts');
                Route::get('featured/{store_id}', 'featuredProducts');
            });
        });

        Route::prefix('ratings')->controller(RattingsController::class)->group(function () {
            Route::post('add', 'add');
            Route::post('update', 'update');
            Route::get('delete/{ratting_id}', 'delete');
        });
    });

    Route::prefix('withdrawal')->controller(WithdrawalRequestsController::class)->group(function () {
        Route::get('getRequests', 'getRequests');
        Route::post('sendRequest', 'sendRequest');
    });

    Route::prefix('orders')->controller(OrdersController::class)->group(function () {
        Route::withoutMiddleware(['jwt.verify'])->group(function () {
            Route::post('new', 'new');
            Route::post('product_by_buyer', 'orderProductByBuyer');
        });
        
        Route::get('/logged-in/buyer', 'showLoggedinBuyerOrders');
        Route::get('seller', 'sellerOrders');
        Route::get('driver_orders/{driver_id}', 'driverOrders');
        Route::get('assign_order', 'assignOrder');
        Route::get('cancel_order', 'cancelOrder');
        Route::get('update_assign', 'updateAssign');
        Route::post('customer_cancel_order', 'customerCancelOrder');
        Route::post('update', 'updateOrder');
        Route::post('estimated-time/{id}', 'storeEstimatedTime');
        Route::get('get-order-details/{id}', 'getOrderDetailsTwo');
        Route::get('products-of-recent-order', 'productsOfRecentOrder');
    });

    Route::prefix('driver')->controller(DriverController::class)->group(function () {
        Route::get('info/{id}', 'info');
        Route::post('add-lat-lon', 'addLatLon');
        Route::get('withdrawable-balance', 'getWithdrawalBalance');
        Route::get('request-withdrawal-balance', 'submitWithdrawal');
        Route::post('bank-details', 'submitBankAccountDetails');
        Route::get('all-withdrawals', 'driverAllWithdrawalRequests');
        Route::post('check_verification_code/{order_id}', 'checkVerificationCode');
        Route::post('driver_failed_to_enter_code/{order_id}', 'driverFailedToEnterCode');
        Route::withoutMiddleware('jwt.verify')->group(function () {
            Route::post('register', 'registerDriver');
            Route::post('login', 'loginDriver');
        });
    });

    Route::prefix('promocodes')->controller(PromoCodesController::class)->group(function () {
        Route::post('validate', 'promocodesValidate');
        Route::post('fetch_promocode_info', 'fetchPromocodeInfo');
        Route::get('all', 'allPromocodes');
    });

    Route::prefix('referral')->controller(ReferralCodeRelationController::class)->group(function () {
        Route::post('validate', 'validateReferral');
        Route::post('insert', 'insertReferrals');
        Route::get('details_by_id/{referral_relation_id}', 'fetchReferralRelationDetails');
        Route::post('update/referral_usable/status', 'updateReferralStatus');
    });

    Route::prefix('wallet')->group(function () {
        // Route::post('/update', [WalletController::class, 'update']);
    });

    // Route::get('keys', [AuthController::class, 'keys']);
});
/*
|--------------------------------------------------------------------------
| Page API Routes
|--------------------------------------------------------------------------
*/
Route::get('page', [PagesController::class, 'getPage']);
/*
|--------------------------------------------------------------------------
| Random API Routes
|--------------------------------------------------------------------------
*/
Route::controller(StripeServices::class)->group(function () {
    Route::get('payment_intent', 'createPaymentIntent');
    Route::get('payment_intent/request_incremental_authorization_support', 'requestIncrementalAuthorizationSupport');
    Route::get('payment_intent/perform_incremental_authorization', 'performIncrementalAuthorization');
    Route::get('payment_intent/capture', 'capturePaymentIntent');

    Route::get('payment_intent/test', 'createPaymentIntent');
    Route::get('payment_intent/test/request_incremental_authorization_support', 'requestIncrementalAuthorizationSupport');
    Route::get('payment_intent/test/perform_incremental_authorization', 'performIncrementalAuthorization');
    Route::get('payment_intent/test/capture', 'capturePaymentIntent');
});

Route::get('time', function () {
    return response()->json([
        'data' => time(),
        'status' => true,
        'message' => ''
    ], 200);
});

Route::get('generate_hash', function () {
    return response()->json([
        'data' => Hash::make($_REQUEST['password']),
        'status' => true,
        'message' => ''
    ], 200);
});

Route::get('cache/remove', function () {
    return response()->json([
        'data' => [],
        'status' => true,
        'message' => (Cache::flush()) ? config('constants.CACHE_REMOVED_SUCCESSFULLY') : config('constants.CACHE_REMOVED_FAILED')
    ], 200);
});

Route::fallback(function () {
    return response()->json([
        'data' => [],
        'status' => false,
        'message' => 'API Not Found.'
    ], 404);
});
