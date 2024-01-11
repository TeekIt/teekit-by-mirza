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
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalRequestsController;
use App\Products;
use App\Services\JsonResponseCustom;
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

Route::get('products', function () {
    try {
        return response()->json([
            'data' => Products::all(),
            'status' => true,
            'message' => ''
        ], 200);
    } catch (Throwable $error) {
        report($error);
        return JsonResponseCustom::getApiResponse(
            [],
            false,
            $error,
            config('constants.HTTP_SERVER_ERROR')
        );
    }
});

Route::get('/', function () {
    return 'Teek it API Routes Are Working Fine :)';
});
/*
|--------------------------------------------------------------------------
| Authentication API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('register_google', [AuthController::class, 'registerGoogle']);
    Route::get('verify', [AuthController::class, 'verify']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('login_google', [AuthController::class, 'loginGoogle']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('update', [AuthController::class, 'updateUser']);
    Route::post('updateStatus', [AuthController::class, 'updateStatus']);
    Route::get('me', [AuthController::class, 'me']); /////////////////////////////////////////////////////////////////////////////////////////////////
    Route::get('delivery_boys', [AuthController::class, 'deliveryBoys']);
    Route::get('get_user/{user_id}', [AuthController::class, 'getUserDetails']);
    Route::post('user/delete', [AuthController::class, 'deleteUser']);
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
Route::prefix('qty')->group(function () {
    Route::get('all', [QtyController::class, 'all']);
    Route::get('product/{store_id}', [QtyController::class, 'getByStoreId']);
    Route::get('product/{store_id}/{prod_id}', [QtyController::class, 'getById']);
    Route::post('update/{prod_id}', [QtyController::class, 'updateById']);
    Route::post('insert_parent_qty_to_child', [QtyController::class, 'insertParentQtyToChild'])->middleware('jwt.verify');
    // Route::get('multi-curl', 'QtyController@multiCURL');
    // Route::get('shifting-qty', 'QtyController@shiftQtyInProductsToQtyTable');
});
/*
|--------------------------------------------------------------------------
| Category API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('category')->group(function () {
    Route::post('add', [CategoriesController::class, 'add']);
    Route::post('update/{product_id}', [CategoriesController::class, 'update']);
    Route::get('view/{category_id}', [CategoriesController::class, 'products']);
    Route::get('get-stores-by-category/{category_id}', [CategoriesController::class, 'stores']);
    Route::get('all', [CategoriesController::class, 'all']);
});
/*
|--------------------------------------------------------------------------
| Seller API Routes Without JWT Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('sellers')->group(function () {
    Route::get('/', [UsersController::class, 'sellers']);
    Route::get('{seller_id}/{product_name}', [AuthController::class, 'searchSellerProducts']);
});
/*
|--------------------------------------------------------------------------
| Notifications API Routes Without JWT Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationsController::class, 'getNotifications']);
    Route::post('save_token', [NotificationsController::class, 'saveToken']);
    Route::get('delete/{notification_id}', [NotificationsController::class, 'deleteNotification']);
    Route::post('send_test', [NotificationsController::class, 'notificationSendTest']);
});
/*
|--------------------------------------------------------------------------
| API Routes With JWT Authentication
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.verify'])->group(function () {
    Route::prefix('product')->group(function () {
        Route::post('add', [ProductsController::class, 'add']);
        Route::post('add/bulk', [ProductsController::class, 'importProductsAPI']);
        Route::post('update/{product_id}', [ProductsController::class, 'update']);
        Route::post('update_price_qty/bulk', [ProductsController::class, 'updatePriceAndQtyBulk']);
        Route::get('delete/{product_id}', [ProductsController::class, 'delete']);
        Route::get('delete_image/{image_id}/{product_id}', [ProductsController::class, 'deleteImage']);
        Route::post('ratings/add', [RattingsController::class, 'add']);
        Route::post('ratings/update', [RattingsController::class, 'update']);
        Route::get('ratings/delete/{ratting_id}', [RattingsController::class, 'delete']);

        Route::withoutMiddleware(['jwt.verify'])->group(function () {
            Route::get('all', [ProductsController::class, 'all']);
            Route::post('search', [ProductsController::class, 'search']);
            Route::get('view', [ProductsController::class, 'view']);
            Route::post('view/bulk', [ProductsController::class, 'bulkView']);
            Route::get('seller', [ProductsController::class, 'sellerProducts']);
            Route::get('sortbyprice', [ProductsController::class, 'sortByPrice']);
            Route::get('sortByLocation', [ProductsController::class, 'sortByLocation']);
            Route::post('recheck_products', [OrdersController::class, 'recheckProducts']);
            Route::get('featured/{store_id}', [ProductsController::class, 'featuredProducts']);
            Route::get('drop-qty-column', [ProductsController::class, 'dropProductsTableQtyColumn']);
        });
    });

    Route::prefix('withdrawal')->group(function () {
        Route::get('getRequests', [WithdrawalRequestsController::class, 'getRequests']);
        Route::post('sendRequest', [WithdrawalRequestsController::class, 'sendRequest']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [OrdersController::class, 'showBuyerOrders']);
        Route::post('new', [OrdersController::class, 'new']);
        Route::get('seller', [OrdersController::class, 'sellerOrders']);
        Route::get('delivery_boy_orders/{delivery_boy_id}', [OrdersController::class, 'deliveryBoyOrders']);
        Route::get('assign_order', [OrdersController::class, 'assignOrder']);
        Route::get('cancel_order', [OrdersController::class, 'cancelOrder']);
        Route::get('update_assign', [OrdersController::class, 'updateAssign']);
        Route::post('customer_cancel_order', [OrdersController::class, 'customerCancelOrder']);
        Route::post('update', [OrdersController::class, 'updateOrder']);
        Route::post('estimated-time/{id}', [OrdersController::class, 'storeEstimatedTime']);
        Route::get('get-order-details/{id}', [OrdersController::class, 'getOrderDetailsTwo']);
        Route::get('products-of-recent-order', [OrdersController::class, 'productsOfRecentOrder']);
    });

    Route::prefix('driver')->group(function () {
        Route::get('info/{id}', [DriverController::class, 'info']);
        Route::post('add-lat-lon', [DriverController::class, 'addLatLon']);
        Route::get('withdrawable-balance', [DriverController::class, 'getWithdrawalBalance']);
        Route::get('request-withdrawal-balance', [DriverController::class, 'submitWithdrawal']);
        Route::post('bank-details', [DriverController::class, 'submitBankAccountDetails']);
        Route::get('all-withdrawals', [DriverController::class, 'driverAllWithdrawalRequests']);
        Route::post('check_verification_code/{order_id}', [DriverController::class, 'checkVerificationCode']);
        Route::post('driver_failed_to_enter_code/{order_id}', [DriverController::class, 'driverFailedToEnterCode']);

        Route::withoutMiddleware('jwt.verify')->group(function () {
            Route::post('register', [DriverController::class, 'registerDriver']);
            Route::post('login', [DriverController::class, 'loginDriver']);
        });
    });

    Route::prefix('promocodes')->group(function () {
        Route::post('validate', [PromoCodesController::class, 'promocodesValidate']);
        Route::post('fetch_promocode_info', [PromoCodesController::class, 'fetchPromocodeInfo']);
        Route::get('all', [PromoCodesController::class, 'allPromocodes']);
    });

    Route::prefix('referral')->group(function () {
        Route::post('validate', [ReferralCodeRelationController::class, 'validateReferral']);
        Route::get('insert', [ReferralCodeRelationController::class, 'insertReferrals']);
        Route::get('details_by_id/{referral_relation_id}', [ReferralCodeRelationController::class, 'fetchReferralRelationDetails']);
        Route::post('update/referral_usable/status', [ReferralCodeRelationController::class, 'updateReferralStatus']);
    });

    Route::prefix('wallet')->group(function () {
        // Route::post('/update', [WalletController::class, 'update']);
    });

    Route::get('keys', [AuthController::class, 'keys']);
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
Route::get('payment_intent', [StripeServices::class, 'createPaymentIntent']);
Route::get('payment_intent/request_incremental_authorization_support', [StripeServices::class, 'requestIncrementalAuthorizationSupport']);
Route::get('payment_intent/perform_incremental_authorization', [StripeServices::class, 'performIncrementalAuthorization']);
Route::get('payment_intent/capture', [StripeServices::class, 'capturePaymentIntent']);

Route::get('payment_intent/test', [StripeServices::class, 'createPaymentIntent']);
Route::get('payment_intent/test/request_incremental_authorization_support', [StripeServices::class, 'requestIncrementalAuthorizationSupport']);
Route::get('payment_intent/test/perform_incremental_authorization', [StripeServices::class, 'performIncrementalAuthorization']);
Route::get('payment_intent/test/capture', [StripeServices::class, 'capturePaymentIntent']);

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
