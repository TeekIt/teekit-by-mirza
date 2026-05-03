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
use App\Http\Controllers\Api\v2\RequestedDeliveryController;
use App\Http\Controllers\Api\v2\StuartDeliveryController;
use App\Http\Controllers\Api\v2\SuperWallPackageController;
use App\Http\Controllers\Api\v2\VanController;
use App\Http\Controllers\Api\v2\VanOperativeProductUsageController;
use App\Http\Controllers\Api\v2\VanProductController;
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

Route::middleware('transaction.wrapper')->group(function () {

    Route::get('/', fn() => 'Teek it API Routes Are Working Fine 😃');
    /*
     *********************************************************************** 
     * Authentication API Routes
     ***********************************************************************
     */
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('register', 'registerBuyer');
        Route::post('register_google', 'registerBuyerFromGoogle');
        Route::post('login', 'loginUser');
        Route::post('login_google', 'loginBuyerFromGoogle');
        Route::get('verify', 'verify');

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
    /*
     *********************************************************************** 
     * Password API Routes
     ***********************************************************************
     */
    Route::prefix('password')->group(function () {
        Route::post('email', [ForgotPasswordController::class, 'getResetToken']);
        Route::post('reset', [ResetPasswordController::class, 'reset']);
    });
    /*
     *********************************************************************** 
     * Qty API Routes
     ***********************************************************************
     */
    Route::prefix('qty')->controller(QtyController::class)->group(function () {
        Route::get('product/{storeId}/{prodId}', 'getById');
        /* 
        Route::post('insert_parent_qty_to_child', 'insertParentQtyToChild')->middleware('jwt.verify');
        Route::get('multi-curl', 'multiCURL'); 
        */
    });
    /*
     *********************************************************************** 
     * Category API Routes
     ***********************************************************************
     */
    Route::prefix('category')->controller(CategoriesController::class)->group(function () {
        Route::get('list', 'list');
        Route::get('list/{categoryId}/products', 'listProductsByCategoryId');
        Route::get('list/{categoryId}/sellers', 'listSellersByCategoryId');
    });
    /*
     *********************************************************************** 
     * Seller API Routes
     ***********************************************************************
     */
    Route::prefix('seller')->controller(SellerController::class)->group(function () {
        Route::get('list', 'list');
        Route::post('save/stripe_account_id', 'saveStripeAccountId');
    });
    /*
     *********************************************************************** 
     * Notifications API Routes
     ***********************************************************************
     */
    Route::prefix('notification')->controller(NotificationsController::class)->group(function () {
        Route::post('save_token', 'saveToken');
    });
    /*
     *********************************************************************** 
     * Stripe API Routes
     ***********************************************************************
     */
    Route::prefix('stripe')->controller(StripeController::class)->group(function () {
        Route::prefix('payment_intent')->group(function () {
            Route::get('create', 'store');
            Route::get('capture', 'capturePaymentIntent');
            Route::get('refund', 'refundPaymentIntent');
        });

        Route::get('request_payment_authorization', 'requestPaymentAuthorization');
        Route::get('request_incremental_authorization_support', 'requestIncrementalAuthorizationSupport');
        Route::get('perform_incremental_authorization', 'performIncrementalAuthorization');
    });
    /*
     *********************************************************************** 
     * Page API Routes
     ***********************************************************************
     */
    Route::get('page', [PagesController::class, 'getPage']);
    /*
    *********************************************************************** 
    * Van API Routes (With Role Based Guards)
    ***********************************************************************
    */
    Route::prefix('van')->middleware('jwt.verify:van')->group(function () {
        Route::controller(VanController::class)->group(function () {
            Route::post('login', 'loginVan')->withoutMiddleware('jwt.verify:van');
            Route::get('list/{vanId}', 'listById');
            Route::get('dashboard/stats', 'dashboardStats');
            Route::get('activity/recent', 'recentActivities');
        });

        Route::prefix('product')->controller(VanProductController::class)->group(function () {
            Route::get('list', 'list');
            Route::get('list/{productId}', 'listById')->whereNumber('productId');
            Route::get('search', 'search');
        });

        Route::prefix('operative_product_usage')->controller(VanOperativeProductUsageController::class)->group(function () {
            Route::post('create', 'store');
            Route::get('list', 'list');
        });
    });
    /*
    *********************************************************************** 
    * API Routes With Simple JWT Authentication (Without Role Based Guards)
    ***********************************************************************
    */
    Route::middleware(['jwt.verify'])->group(function () {
        /*
         *********************************************************************** 
         * Product API Routes
         ***********************************************************************
         */
        Route::prefix('product')->group(function () {
            Route::controller(ProductController::class)->group(function () {
                Route::post('import', 'importProducts');
                Route::post('update_price_qty/bulk', 'updatePriceAndQtyBulk');

                Route::withoutMiddleware(['jwt.verify'])->group(function () {
                    Route::get('list', 'list');
                    Route::post('search', 'search');
                    Route::get('view', 'view');
                    Route::get('seller', 'sellerProducts');
                    Route::get('sortByLocation', 'sortByLocation');
                    Route::post('recheck_products', 'recheckProducts');
                    Route::get('featured/{storeId}', 'featuredProducts');
                });
            });
            /*
            *********************************************************************** 
            * Product Rating API Routes
            ***********************************************************************
            */
            Route::prefix('rating')->controller(RattingController::class)->group(function () {
                Route::post('create', 'store');
                Route::get('delete/{ratingId}', 'delete');
            });
        });
        /*
         *********************************************************************** 
         * Withdrawal API Routes
         ***********************************************************************
         */
        Route::prefix('withdrawal')->controller(WithdrawalRequestController::class)->group(function () {
            Route::get('getRequests', 'getRequests');
            Route::post('sendRequest', 'sendRequest');
        });
        /*
         *********************************************************************** 
         * Order API Routes
         ***********************************************************************
         */
        Route::prefix('order')->controller(OrderController::class)->group(function () {
            Route::withoutMiddleware(['jwt.verify'])->group(function () {
                Route::post('create', 'sotre');
                Route::post('product_by_buyer', 'orderProductByBuyer');
                Route::get('list/{orderId}', 'listById');
            });

            Route::get('logged_in/buyer', 'showLoggedinBuyerOrders');
            Route::get('seller', 'sellerOrders');
            Route::get('driver_orders/{driverId}', 'driverOrders');
            Route::get('assign_order', 'assignOrder');
            Route::get('cancel_order', 'cancelOrder');
            Route::get('update_assign', 'updateAssign');
            Route::post('customer_cancel_order', 'customerCancelOrder');
            Route::post('update', 'updateOrder');
            Route::get('products-of-recent-order', 'productsOfRecentOrder');
        });

        /*
         *********************************************************************** 
         * Driver API Routes
         ***********************************************************************
         */
        Route::prefix('driver')->controller(DriverController::class)->group(function () {
            Route::withoutMiddleware('jwt.verify')->group(function () {
                Route::post('register', 'registerDriver');
                Route::post('login', 'loginDriver');
            });

            Route::get('list/{driverId}', 'listById');
            Route::post('add-lat-lon', 'addLatLon');
            Route::get('withdrawable-balance', 'getWithdrawalBalance');
            Route::get('request-withdrawal-balance', 'submitWithdrawal');
            Route::post('bank-details', 'submitBankAccountDetails');
            Route::get('all-withdrawals', 'driverAllWithdrawalRequests');
            Route::post('check_verification_code/{orderId}', 'checkVerificationCode');
            Route::post('driver_failed_to_enter_code/{orderId}', 'driverFailedToEnterCode');
        });

        /*
         *********************************************************************** 
         * Promo Code API Routes
         ***********************************************************************
         */
        Route::prefix('promocode')->controller(PromoCodeController::class)->group(function () {
            Route::get('list', 'list');
            Route::get('list/{promoCode}', 'listByPromoCode');
            Route::post('validate', 'validatePromoCode');
        });

        /*
         *********************************************************************** 
         * Referral API Routes
         ***********************************************************************
         */
        Route::prefix('referral')->controller(ReferralCodeRelationController::class)->group(function () {
            Route::post('create', 'store');
            Route::post('validate', 'validateReferral');
            Route::get('details_by_id/{referralRelationId}', 'fetchReferralRelationDetails');
            Route::post('update/referral_usable/status', 'updateReferralStatus');
        });

        /* 
        Route::prefix('wallet')->group(function () {
            Route::post('/update', [WalletController::class, 'update']);
        }); 
        */

        /*
         *********************************************************************** 
         * Buyer API Routes
         ***********************************************************************
         */
        Route::prefix('buyer')->controller(BuyerController::class)->group(function () {
            Route::patch('update', 'updateBuyer');
        });
        /*
         *********************************************************************** 
         * Stuart API Routes
         ***********************************************************************
         */
        Route::prefix('stuart')->controller(StuartDeliveryController::class)->group(function () {
            Route::prefix('delivery/job')->group(function () {
                Route::post('create', 'store');
                Route::get('pricing', 'getDeliveryJobPricing');
                Route::get('track/{jobId}', 'trackDeliveryJob');
            });
        });
        /*
         *********************************************************************** 
         * Gophr API Routes
         ***********************************************************************
         */
        Route::prefix('gophr')->controller(GophrDeliveryController::class)->group(function () {
            Route::prefix('delivery/job')->group(function () {
                Route::post('create', 'store');
                Route::get('pricing', 'getDeliveryJobPricing');
                Route::get('track/{jobId}', 'trackDeliveryJob');
            });
        });
        /*
         *********************************************************************** 
         * Delivery API Routes
         ***********************************************************************
         */
        Route::prefix('delivery')->controller(RequestedDeliveryController::class)->group(function () {
            Route::get('list/{creatorId}', 'listByCreatorId');
        });
        /*
         *********************************************************************** 
         * Super Wall Package API Routes
         ***********************************************************************
         */
        Route::prefix('super_wall_package')->controller(SuperWallPackageController::class)->group(function () {
            Route::post('create', 'store');
            Route::get('list', 'list');
            Route::get('list/{superWallPackageId}', 'listById');
            Route::put('update/{superWallPackageId}', 'update');
            Route::delete('delete/{superWallPackageId}', 'destroy');
        });

        // Route::get('keys', [AuthController::class, 'keys']);
    });
});

/*
*********************************************************************** 
* Random API Routes
***********************************************************************
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
