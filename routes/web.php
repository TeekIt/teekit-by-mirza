<?php

use App\Http\Controllers\Admin\UserAndRoleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Livewire\Admin\ParentSellersLivewire;
use App\Http\Livewire\Admin\ReferralCodesLivewire;
use App\Http\Livewire\Sellers\InventoryLivewire;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PromoCodesController;
use App\Http\Controllers\StuartDeliveryController;
use App\Http\Controllers\UsersController;
use App\Http\Livewire\Admin\ChildSellersLivewire;
use App\Http\Livewire\Admin\CustomersLivewire;
use App\Http\Livewire\Admin\DriversLivewire;
use App\Http\Livewire\Sellers\OrdersFromOtherSellersLivewire;
use App\Http\Livewire\Sellers\OrdersLivewire;
use App\Http\Livewire\Sellers\SellerDashboardLivewire;
use App\Http\Livewire\Sellers\Settings\UserGeneralSettings;
use App\Http\Livewire\Sellers\WithdrawalLivewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| For Adding Default Authentication Routes:-
|   * Registering a new user Route::post('/register', 'Auth\RegisterController@register');
|   * Authenticating a user Route::post('/login', 'Auth\LoginController@login');
|   * Resetting a user's password Route::post('/password/reset', 'Auth\ResetPasswordController@reset')
|   * Confirming a user's email address 'Auth\VerificationController'
|--------------------------------------------------------------------------
*/

Auth::routes();

Route::get('auth/verify', [AuthController::class, 'verify']);
/*
|--------------------------------------------------------------------------
| Home Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
/*
|--------------------------------------------------------------------------
| User Settings Routes
|--------------------------------------------------------------------------
*/
Route::prefix('settings')->group(function () {
    Route::post('/user_info/update', [HomeController::class, 'userInfoUpdate'])->name('admin.userinfo.update');
    Route::get('/payment', [HomeController::class, 'paymentSettings'])->name('setting.payment');
    Route::post('/payment/update', [HomeController::class, 'paymentSettingsUpdate'])->name('payment_settings_update');
    Route::post('/user_img/update', [HomeController::class, 'userImgUpdate'])->name('user_img_update');
    // Route::post('/location_update', [HomeController::class, 'locationUpdate'])->name('location_update');
    Route::post('/password/update', [HomeController::class, 'adminPasswordUpdate'])->name('password_update');
    Route::get('/change_settings/{setting_name}/{value}', [HomeController::class, 'changeSettings'])->name('change_settings')->where(['setting_name' => '^[a-z_]*$', 'value' => '[0-9]+']);
});
/*
|--------------------------------------------------------------------------
| Imp/Exp Products Routes
|--------------------------------------------------------------------------
*/
Route::get('/exportProducts', [ProductsController::class, 'exportProducts'])->name('exportProducts');
Route::post('/importProducts', [HomeController::class, 'importProducts'])->name('importProducts');
/*
|--------------------------------------------------------------------------
| Orders Routes
|--------------------------------------------------------------------------
*/
Route::prefix('orders')->group(function () {
    Route::get('/ready_state/{order_id}', [HomeController::class, 'changeOrderStatus'])->name('accept_order');
    Route::get('/mark_as_delivered/{order_id}', [HomeController::class, 'markAsDelivered'])->name('mark_as_delivered');
    Route::get('/mark_as_completed/{order_id}', [HomeController::class, 'markAsCompleted'])->name('mark_as_completed');
    Route::get('/cancel/{order_id}', [HomeController::class, 'cancelOrder'])->name('cancel_order');
    Route::get('/{order_id}/remove/{item_id}/product/{product_price}/{product_qty}', [HomeController::class, 'removeProductFromOrder'])->name('remove_order_product');
    Route::get('/verify/{order_id}', [HomeController::class, 'clickToVerify'])->name('verify_order');
});

Route::middleware(['auth', 'auth.sellers'])->prefix('seller')->group(function () {

    Route::get('/dashboard', SellerDashboardLivewire::class)->name('seller.dashboard');

    Route::prefix('inventory')->group(function () {
        Route::get('/', InventoryLivewire::class)->name('seller.inventory');

        Route::controller(ProductsController::class)->group(function () {
            Route::post('/add', 'addSingleInventory')->name('seller.add.single.inventory');
            Route::get('/edit/{product_id}', 'editInventoryView')->name('seller.edit.inventory.form');
            Route::post('/update/{product_id}', 'updateInventory')->name('seller.edit.inventory');
            Route::get('/image/delete/{image_id}', 'deleteImg')->name('seller.deleteImg');
        });

        Route::controller(HomeController::class)->group(function () {
            Route::get('/add', 'inventoryAdd')->name('seller.add.single.inventory.form');
            Route::get('/add_bulk', 'inventoryAddBulk')->name('seller.add.bulk.inventory');
        });
        // Route::post('/update_child_qty', [QtyController::class, 'updateChildQty'])->name('update_child_qty');
    });

    Route::prefix('orders')->group(function () {
        Route::get('/from-other-sellers', OrdersFromOtherSellersLivewire::class)->name('seller.orders.from.others');
        Route::get('/count', [HomeController::class, 'countSellerOrders'])->name('seller.orders.count');
        Route::get('/{request_order_id?}', OrdersLivewire::class)->name('seller.orders');
    });

    Route::get('/withdrawal', WithdrawalLivewire::class)->name('seller.withdrawal');

    Route::prefix('settings')->group(function () {
        Route::get('/general', UserGeneralSettings::class)->name('seller.settings.general');
        Route::post('/update-location', [UsersController::class, 'updateStoreLocation'])->name('seller.settings.update.location');
        Route::post('/update-required-info', [UsersController::class, 'updateSellerRequiredInfo'])->name('seller.update.required.info');
    });
});
/*
|--------------------------------------------------------------------------
| Withdrawal Routes
|--------------------------------------------------------------------------
*/
Route::controller(HomeController::class)->group(function () {
    Route::get('/withdrawals', 'withdrawals')->name('withdrawals');
    Route::post('/withdrawals', 'withdrawalsRequest')->name('withdrawal.request');
    Route::get('/withdrawals-drivers', 'withdrawalDrivers')->name('withdrawals.drivers');
});
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'auth.admin'])->group(function () {
    Route::get('/referralcodes', ReferralCodesLivewire::class)->name('admin.referralcodes');
    Route::get('/sellers/parent', ParentSellersLivewire::class)->name('admin.sellers.parent');
    Route::get('/sellers/child', ChildSellersLivewire::class)->name('admin.sellers.child');
    Route::get('/customers', CustomersLivewire::class)->name('admin.customers');
    Route::get('/drivers', DriversLivewire::class)->name('admin.test.drivers');

    Route::controller(NotificationsController::class)->group(function () {
        Route::prefix('notification')->group(function () {
            Route::get('/home', 'notificationHome')->name('admin.notification.home');
            Route::post('/send', 'notificationSend')->name('admin.notification.send');
        });
    });

    Route::controller(UsersController::class)->group(function () {
        Route::prefix('delete')->group(function () {
            Route::get('/users', 'adminUsersDel')->name('admin.del.users');
            Route::get('/drivers', 'adminDriversDel')->name('admin.del.drivers');
        });
    });

    Route::controller(HomeController::class)->group(function () {
        Route::post('/update/pages', 'updatePages')->name('admin.update.pages');
    });
});

Route::controller(StuartDeliveryController::class)->group(function () {
    Route::prefix('stuart')->group(function () {
        Route::prefix('job')->group(function () {
            Route::post('/creation/', 'stuartJobCreation')->name('stuart.job.creation');
            Route::post('/status', 'stuartJobStatus')->name('stuart.job.status');
        });
    });
});

Route::get('/drivers', [HomeController::class, 'adminDrivers'])->name('admin.drivers');
Route::get('/promocodes/home', [PromoCodesController::class, 'promocodesHome'])->name('admin.promocodes.home');
Route::post('/promocodes/add', [PromoCodesController::class, 'promocodesAdd'])->name('admin.promocodes.add');
Route::get('/promocodes/delete', [PromoCodesController::class, 'promoCodesDel'])->name('admin.promocodes.del');
Route::post('/promocodes/{id}/update', [PromoCodesController::class, 'promoCodesUpdate'])->name('admin.promocodes.update');
Route::get('/aorders', [HomeController::class, 'adminOrders'])->name('admin.orders');
Route::get('/aorders/verified', [HomeController::class, 'adminOrdersVerified'])->name('admin.orders.verified');
Route::get('/aorders/unverified', [HomeController::class, 'adminOrdersUnverified'])->name('admin.orders.unverified');
Route::get('/aorders/delete', [HomeController::class, 'adminOrdersDel'])->name('admin.del.orders');
Route::get('/complete-orders', [HomeController::class, 'completeOrders'])->name('complete.order');
Route::get('/mark-complete-order/{id}', [HomeController::class, 'markCompleteOrder'])->name('mark.complete.order');
Route::get('/asetting', [HomeController::class, 'aSetting'])->name('admin.setting');
Route::get('/acategories', [HomeController::class, 'allCat'])->name('admin.categories');
Route::post('/acategories/{id}/update', [HomeController::class, 'updateCat'])->name('update_cat');
Route::post('/acategories/add_cat', [HomeController::class, 'addCat'])->name('add_cat');
Route::get('/acategories/delete_cat/{id}', [HomeController::class, 'deleteCat'])->name('delete_cat');
Route::get('/queries', [HomeController::class, 'adminQueries'])->name('admin.queries');
Route::get('/customer/{user_id}/details', [HomeController::class, 'adminCustomerDetails'])->name('customer_details');
Route::get('/driver/{driver_id}/details', [HomeController::class, 'adminDriverDetails'])->name('driver_details');
Route::get('/store/application-fee/{user_id}/{application_fee}', [UserAndRoleController::class, 'updateApplicationFee'])->name('application_fee');
Route::get('/users/{user_id}/status/{status}', [HomeController::class, 'changeUserStatus'])->name('change_user_status');
Route::post('/store_info/update', [HomeController::class, 'updateStoreInfo'])->name('admin.image.update');
