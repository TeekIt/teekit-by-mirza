<?php

use App\Http\Controllers\Api\v1\BuyerController;
use App\Http\Controllers\Api\v1\CategoriesController;
use App\Http\Controllers\Api\v1\DriverController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Api\v1\PagesController;
use App\Http\Controllers\Api\v1\ProductController;
use App\Http\Controllers\Api\v1\PromoCodeController;
use App\Http\Controllers\Api\v1\QtyController;
use App\Http\Controllers\Api\v1\RattingController;
use App\Http\Controllers\Api\v1\ReferralCodeRelationController;
use App\Http\Controllers\Api\v1\SellerController;
use App\Http\Controllers\Api\v1\StripeController;
use App\Http\Controllers\Api\v1\WithdrawalRequestController;
use App\Http\Controllers\Api\v2\GophrDeliveryController;
use App\Http\Controllers\Api\v2\StuartDeliveryController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\NotificationsController;
use App\Services\JsonResponseServices;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
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

Route::get('/', fn () => 'Teek it API Routes Are Working Fine 😃');
/*
|--------------------------------------------------------------------------
| Authentication API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'loginBuyer');
    Route::post('register', 'registerBuyer');
    Route::get('verify', 'verify');
    Route::post('login_google', 'loginBuyerFromGoogle');
    Route::post('register_google', 'registerBuyerFromGoogle');

    Route::middleware(['jwt.verify'])->group(function () {
        Route::post('change-password', 'changePassword');
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
        Route::post('updateStatus', 'updateStatus');
        Route::get('get_user/{userId}', 'getUserDetails');
        Route::post('user/delete', 'deleteUser');
        Route::get('me', 'me');
    });
});

Route::prefix('password')->group(function () {
    Route::post('email', [ForgotPasswordController::class, 'getResetToken']);
    Route::post('reset', [ResetPasswordController::class, 'reset']);
});
/*
|--------------------------------------------------------------------------
| Qty API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('qty')->controller(QtyController::class)->group(function () {
    Route::get('product/{store_id}/{prod_id}', 'getById');
    /* Route::post('insert_parent_qty_to_child', 'insertParentQtyToChild')->middleware('jwt.verify');
    Route::get('multi-curl', 'multiCURL'); */
});
/*
|--------------------------------------------------------------------------
| Category API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('category')->controller(CategoriesController::class)->group(function () {
    Route::get('{categoryId}/products', 'productsByCategory');
    Route::get('get-stores-by-category', 'sellers');
    Route::get('all', 'all');
});
/*
|--------------------------------------------------------------------------
| Seller API Routes Without JWT Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('sellers')->controller(SellerController::class)->group(function () {
    Route::get('/', 'sellers');
    Route::post('save/stripe_account_id', 'saveStripeAccountId');
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
| Stripe API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('stripe')->controller(StripeController::class)->group(function () {
    Route::prefix('payment_intent')->group(function () {
        Route::get('create', 'createPaymentIntent');
        Route::get('capture', 'capturePaymentIntent');
        Route::get('refund', 'refundPaymentIntent');
    });

    Route::get('request_payment_authorization', 'requestPaymentAuthorization');
    Route::get('request_incremental_authorization_support', 'requestIncrementalAuthorizationSupport');
    Route::get('perform_incremental_authorization', 'performIncrementalAuthorization');
});
/*
|--------------------------------------------------------------------------
| Page API Routes
|--------------------------------------------------------------------------
*/
Route::get('page', [PagesController::class, 'getPage']);
/*
|--------------------------------------------------------------------------
| API Routes With JWT Authentication
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.verify'])->group(function () {
    Route::prefix('product')->group(function () {
        Route::controller(ProductController::class)->group(function () {
            Route::post('add/bulk', 'importProducts');
            Route::post('update_price_qty/bulk', 'updatePriceAndQtyBulk');

            Route::withoutMiddleware(['jwt.verify'])->group(function () {
                Route::get('all', 'all');
                Route::post('search', 'search');
                Route::get('view', 'view');
                Route::get('seller', 'sellerProducts');
                Route::get('sortByLocation', 'sortByLocation');
                Route::post('recheck_products', 'recheckProducts');
                Route::get('featured/{store_id}', 'featuredProducts');
            });
        });

        Route::prefix('ratings')->controller(RattingController::class)->group(function () {
            Route::post('add', 'add');
            Route::get('delete/{ratting_id}', 'delete');
        });
    });

    Route::prefix('withdrawal')->controller(WithdrawalRequestController::class)->group(function () {
        Route::get('getRequests', 'getRequests');
        Route::post('sendRequest', 'sendRequest');
    });

    Route::prefix('orders')->controller(OrderController::class)->group(function () {
        Route::withoutMiddleware(['jwt.verify'])->group(function () {
            Route::post('new', 'new');
            Route::post('product_by_buyer', 'orderProductByBuyer');
            Route::get('get-order-details/{id}', 'getOrderDetailsForApi');
        });

        Route::get('logged_in/buyer', 'showLoggedinBuyerOrders');
        Route::get('seller', 'sellerOrders');
        Route::get('driver_orders/{driver_id}', 'driverOrders');
        Route::get('assign_order', 'assignOrder');
        Route::get('cancel_order', 'cancelOrder');
        Route::get('update_assign', 'updateAssign');
        Route::post('customer_cancel_order', 'customerCancelOrder');
        Route::post('update', 'updateOrder');
        Route::get('products-of-recent-order', 'productsOfRecentOrder');
    });

    Route::prefix('driver')->controller(DriverController::class)->group(function () {
        Route::withoutMiddleware('jwt.verify')->group(function () {
            Route::post('register', 'registerDriver');
            Route::post('login', 'loginDriver');
        });

        Route::get('info/{id}', 'info');
        Route::post('add-lat-lon', 'addLatLon');
        Route::get('withdrawable-balance', 'getWithdrawalBalance');
        Route::get('request-withdrawal-balance', 'submitWithdrawal');
        Route::post('bank-details', 'submitBankAccountDetails');
        Route::get('all-withdrawals', 'driverAllWithdrawalRequests');
        Route::post('check_verification_code/{order_id}', 'checkVerificationCode');
        Route::post('driver_failed_to_enter_code/{order_id}', 'driverFailedToEnterCode');
    });

    Route::prefix('promocodes')->controller(PromoCodeController::class)->group(function () {
        Route::get('all', 'allPromoCodes');
        Route::post('validate', 'validatePromoCodes');
        Route::post('fetch_promocode_info', 'fetchPromoCodeInfo');
    });

    Route::prefix('referral')->controller(ReferralCodeRelationController::class)->group(function () {
        Route::post('validate', 'validateReferral');
        Route::post('insert', 'insertReferrals');
        Route::get('details_by_id/{referral_relation_id}', 'fetchReferralRelationDetails');
        Route::post('update/referral_usable/status', 'updateReferralStatus');
    });

    /* Route::prefix('wallet')->group(function () {
        Route::post('/update', [WalletController::class, 'update']);
    }); */

    Route::prefix('buyer')->controller(BuyerController::class)->group(function () {
        Route::patch('update', 'updateBuyer');
    });

    Route::prefix('stuart')->controller(StuartDeliveryController::class)->group(function () {
        Route::prefix('delivery/job')->group(function () {
            Route::post('create', 'createDeliveryJob');
            Route::get('pricing', 'getDeliveryJobPricing');
            Route::get('track/{job_id}', 'trackDeliveryJob');
        });
    });

    Route::prefix('gophr')->controller(GophrDeliveryController::class)->group(function () {
        Route::prefix('delivery/job')->group(function () {
            Route::post('create', 'createDeliveryJob');
            Route::get('pricing', 'getDeliveryJobPricing');
            Route::get('track/{job_id}', 'trackDeliveryJob');
        });
    });

    // Route::get('keys', [AuthController::class, 'keys']);
});
/*
|--------------------------------------------------------------------------
| Random API Routes
|--------------------------------------------------------------------------
*/
Route::get('env', function () {
    return JsonResponseServices::getApiResponse(
        ['current_env' => App::environment()],
        config('constants.TRUE_STATUS'),
        '',
        config('constants.HTTP_OK')
    );
});

Route::get('generate_hash', function () {
    return JsonResponseServices::getApiResponse(
        Hash::make($_REQUEST['password']),
        config('constants.TRUE_STATUS'),
        '',
        config('constants.HTTP_OK')
    );
});

Route::get('cache/remove', function () {
    return JsonResponseServices::getApiResponse(
        [],
        config('constants.TRUE_STATUS'),
        (dd(Cache::flush())) ? config('constants.CACHE_REMOVED_SUCCESSFULLY') : config('constants.CACHE_REMOVED_FAILED'),
        config('constants.HTTP_OK')
    );
});

Route::fallback(function () {
    return JsonResponseServices::getApiResponse(
        [],
        config('constants.FALSE_STATUS'),
        'API Not Found.',
        config('constants.HTTP_NOT_FOUND')
    );
});
