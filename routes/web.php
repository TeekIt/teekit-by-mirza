<?php

use App\Http\Controllers\Admin\UserAndRoleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Livewire\Admin\ParentSellersLiveWire;
use App\Http\Livewire\Admin\ReferralCodes;
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
use App\Http\Livewire\Sellers\OrdersLivewire;
use App\Http\Livewire\Sellers\Settings\UserGeneralSettings;
use App\Http\Livewire\Sellers\WithdrawalLivewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

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
    Route::post('/time_update', [HomeController::class, 'timeUpdate'])->name('time_update');
    // Route::post('/location_update', [HomeController::class, 'locationUpdate'])->name('location_update');
    Route::post('/password/update', [HomeController::class, 'passwordUpdate'])->name('password_update');
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

Route::prefix('seller')->middleware(['auth', 'auth.sellers'])->group(function () {
    Route::prefix('inventory')->group(function () {
        Route::get('/', InventoryLivewire::class)->name('seller.inventory');
        Route::get('/add', [HomeController::class, 'inventoryAdd'])->name('seller.inventory.add.single');
        Route::post('/add', [HomeController::class, 'inventoryAddDB'])->name('seller.add_inventory');
        Route::get('/add_bulk', [HomeController::class, 'inventoryAddBulk'])->name('seller.inventory.add.bulk');
        Route::get('/edit/{product_id}', [HomeController::class, 'inventoryEdit'])->name('seller.inventory.edit');
        Route::post('/update/{product_id}', [HomeController::class, 'inventoryUpdate'])->name('seller.update_inventory');
        Route::get('/image/delete/{image_id}', [HomeController::class, 'seller.deleteImg']);
        // Route::post('/update_child_qty', [QtyController::class, 'updateChildQty'])->name('update_child_qty');
    });
    
    Route::get('/orders', OrdersLivewire::class)->name('seller.orders');

    Route::get('/withdrawal', WithdrawalLivewire::class)->name('seller.withdrawal');

    Route::prefix('settings')->group(function () {
        Route::get('/general', UserGeneralSettings::class)->name('seller.settings.general');
        Route::post('/update-location', [UsersController::class, 'updateStoreLocation'])->name('seller.settings.update.location');
    });

});
/*
|--------------------------------------------------------------------------
| Withdrawal Routes
|--------------------------------------------------------------------------
*/
Route::controller(HomeController::class)->group(function () {
    Route::get('/withdrawals', 'withdrawals')->name('withdrawal');
    Route::post('/withdrawals', 'withdrawalsRequest')->name('withdrawal_request');
    Route::get('/withdrawals-drivers', 'withdrawalDrivers')->name('withdrawals.drivers');
});
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::get('/referralcodes', ReferralCodes::class)->name('admin.referralcodes');
    Route::get('/sellers/parent', ParentSellersLiveWire::class)->name('admin.sellers.parent');
    Route::get('/sellers/child', ChildSellersLivewire::class)->name('admin.sellers.child');
    Route::get('/customers', CustomersLivewire::class)->name('admin.customers');
    Route::get('/drivers', DriversLivewire::class)->name('admin.test.drivers');
    Route::get('/drivers_del', [HomeController::class, 'adminDriversDel'])->name('admin.del.drivers');
});
Route::get('/notification/home', [NotificationsController::class, 'notificationHome'])->name('admin.notification.home');
Route::post('/notification/send', [NotificationsController::class, 'notificationSend'])->name('admin.notification.send');

// Route::get('/admin/sellers/parent', [HomeController::class, 'adminParentSellers'])->name('admin.sellers.parent');
// Route::get('/customers', [HomeController::class, 'adminCustomers'])->name('admin.customers');

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
Route::post('/update_pages', [HomeController::class, 'updatePages'])->name('update_pages');
Route::get('/users/{user_id}/status/{status}', [HomeController::class, 'changeUserStatus'])->name('change_user_status');
Route::get('/users_del', [HomeController::class, 'adminUsersDel'])->name('admin.del.users');
// Route::get('/drivers_del', [HomeController::class, 'adminDriversDel'])->name('admin.del.drivers');
Route::post('/store_info/update', [HomeController::class, 'updateStoreInfo'])->name('admin.image.update');
Route::post('/stuart/job/creation/', [StuartDeliveryController::class, 'stuartJobCreation'])->name('stuart.job.creation');
Route::post('/stuart/job/status', [StuartDeliveryController::class, 'stuartJobStatus'])->name('stuart.job.status');

/*
|--------------------------------------------------------------------------
| Total Orders Count Route
|--------------------------------------------------------------------------
*/
Route::get('/my_order_count', [HomeController::class, 'myOrderCount'])->name('my_order_count');
