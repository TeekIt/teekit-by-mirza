<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\Web\v1\AdminController;
use App\Http\Controllers\Web\v1\CategoriesController;
use App\Http\Controllers\Web\v1\HomeController;
use App\Http\Controllers\Web\v1\OrderController;
use App\Http\Controllers\Web\v1\PromoCodeController;
use App\Http\Controllers\Web\v1\SellerController;
use App\Http\Controllers\Web\v1\StuartDeliveryController;
use App\Http\Controllers\Web\v1\UserController;
use App\Http\Controllers\Web\v1\VanController;
use App\Http\Controllers\Web\v2\ProductController;
use App\Http\Controllers\Web\v2\StripeController;
use App\Livewire\Admin\AddVanInventoryLivewire;
use App\Livewire\Admin\CategoriesLivewire;
use App\Livewire\Admin\ChildSellersLivewire;
use App\Livewire\Admin\CustomersLivewire;
use App\Livewire\Admin\ParentSellersLivewire;
use App\Livewire\Admin\ReferralCodesLivewire;
use App\Livewire\Admin\SearchVanInventoriesLivewire;
use App\Livewire\Admin\VanInventoriesLivewire;
use App\Livewire\Admin\VansLivewire;
use App\Livewire\Common\OrdersLivewire;
use App\Livewire\Common\ProductFormLivewire;
use App\Livewire\Company\CompanyDashboardLivewire;
use App\Livewire\Seller\GeneralSettingsLivewire;
use App\Livewire\Seller\InventoryLivewire;
use App\Livewire\Seller\OrdersFromOtherSellersLivewire;
use App\Livewire\Seller\RequestDeliveryFormLivewire;
use App\Livewire\Seller\RequestedDeliveriesLivewire;
use App\Livewire\Seller\SellerDashboardLivewire;
use App\Livewire\Seller\WithdrawalLivewire;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| For Adding Default Authentication Routes:-
|   * Registering a new user Route::post('/register', 'Auth\RegisterController@register');
|   * Authenticating a user Route::post('/login', 'Auth\LoginController@login');
|   * Resetting a user's password Route::post('/password/reset', 'Auth\ResetPasswordController@reset')
|   * Confirming a user's email address 'Auth\VerificationController'
|--------------------------------------------------------------------------
*/

Route::middleware('transaction.wrapper')->group(function () {

    Auth::routes();

    Route::get('auth/verify', [AuthController::class, 'verify']);
    /*
     *********************************************************************** 
     * Home Routes
     ***********************************************************************
     */
    Route::get('/', [HomeController::class, 'index'])->name('home');
    /*
     *********************************************************************** 
     * Buyer Routes
     ***********************************************************************
     */
    Route::view('/buyer/delete_account_steps', 'buyer.delete-account-steps')->name('buyer.delete.account.steps');
    /*
     *********************************************************************** 
     * User Settings Routes
     ***********************************************************************
     */
    Route::prefix('settings')->middleware(['auth', 'auth.sellers'])->controller(HomeController::class)->group(function () {
        Route::get('/payment', 'paymentSettings')->name('setting.payment');
        Route::post('/payment/update', 'paymentSettingsUpdate')->name('payment_settings_update');
        Route::post('/password/update', 'adminPasswordUpdate')->name('password_update');
        Route::get('/change_settings/{setting_name}/{value}', 'changeSettings')->name('change_settings')
            ->where([
                'setting_name' => '^[a-z_]*$',
                'value' => '[0-9]+'
            ]);
    });
    /*
     *********************************************************************** 
     * Import Products Routes
     ***********************************************************************
     */
    Route::post('/importProducts', [ProductController::class, 'importProducts'])->name('importProducts');
    /*
     *********************************************************************** 
     * Orders Routes
     ***********************************************************************
     */
    Route::prefix('orders')->controller(OrderController::class)->group(function () {
        Route::get('/mark_as_delivered/{order_id}', 'markAsDelivered')->name('mark_as_delivered');
        Route::get('/mark_as_completed/{order_id}', 'markAsCompleted')->name('mark_as_completed');
        Route::get('/{order_id}/remove/{item_id}/product/{product_price}/{product_qty}', 'removeProductFromOrder')
            ->name('remove_order_product');
        Route::get('/verify/{order_id}', 'clickToVerify')->name('verify_order');
    });
    /*
     *********************************************************************** 
     * Seller Routes
     ***********************************************************************
     */
    Route::prefix('seller')->middleware(['auth', 'auth.sellers'])->group(function () {

        Route::get('/dashboard', SellerDashboardLivewire::class)->name('seller.dashboard');

        Route::prefix('inventory')->group(function () {
            Route::get('/', InventoryLivewire::class)->name('seller.inventory');

            Route::controller(ProductController::class)->group(function () {
                Route::get('/add', 'addSingleInventoryForm')->name('seller.add.single.inventory.form');
                Route::post('/add', 'addSingleInventory')->name('seller.add.single.inventory');
                Route::get('/edit/{productId}', 'editSingleInventoryForm')->name('seller.edit.inventory.form');
                Route::post('/update/{productId}', 'updateInventory')->name('seller.edit.inventory');
                Route::get('/add_bulk', 'inventoryAddBulk')->name('seller.add.bulk.inventory');
                Route::get('/delete/image/{imageId}', 'deleteImg')->name('seller.delete.img');
            });

            // Route::post('/update_child_qty', [QtyController::class, 'updateChildQty'])->name('update_child_qty');
        });

        Route::prefix('orders')->group(function () {
            Route::get('count', [OrderController::class, 'countSellerOrders'])->name('seller.orders.count');
            Route::get('/from-other-sellers', OrdersFromOtherSellersLivewire::class)->name('seller.orders.from.others');
            Route::get('/from-vans', OrdersLivewire::class)->name('seller.orders.from.vans');
            Route::get('/{requestOrderId?}', OrdersLivewire::class)->name('seller.orders');
        });

        Route::get('/withdrawal', WithdrawalLivewire::class)->name('seller.withdrawal');

        Route::prefix('delivery')->group(function () {
            Route::get('/', RequestedDeliveriesLivewire::class)->name('seller.requested.deliveries');
            Route::get('/request_form', RequestDeliveryFormLivewire::class)->name('seller.request.delivery.form');
        });

        Route::prefix('settings')->group(function () {
            Route::get('/general', GeneralSettingsLivewire::class)->name('seller.settings.general');

            Route::controller(SellerController::class)->group(function () {
                Route::post('/update-location', 'updateStoreLocation')->name('seller.settings.update.location');
                Route::post('/update-required-info', 'updateSellerRequiredInfo')->name('seller.update.required.info');
            });
        });
    });
    /*
     *********************************************************************** 
     * Withdrawal Routes
     ***********************************************************************
     */
    Route::controller(HomeController::class)->group(function () {
        Route::get('/withdrawals', 'withdrawals')->name('withdrawals');
        Route::post('/withdrawals', 'withdrawalsRequest')->name('withdrawal.request');
    });
    /*
     *********************************************************************** 
     * Van Routes
     ***********************************************************************
     */
    Route::prefix('vans')->middleware(['auth', 'auth.company'])->group(function () {
        Route::get('/', VansLivewire::class)->name('vans');
        Route::get('/inventories', VanInventoriesLivewire::class)->name('vans.inventories');
        Route::get('/inventories/order/online', AddVanInventoryLivewire::class)->name('vans.inventories.order.online');
        Route::get('/inventory/{productId}/edit/manually', ProductFormLivewire::class)->name('vans.inventory.edit.manually');
        Route::get('/inventory/add/manually', ProductFormLivewire::class)->name('vans.inventory.add.manually');
        Route::get('/delete', [VanController::class, 'destroy'])->name('vans.del');
        // vans.inventories.del

        Route::prefix('company')->group(function () {
            Route::post('/register', [RegisterController::class, 'registerVanCompany'])->name('vans.company.register')
                ->withoutMiddleware(['auth', 'auth.company']);

            Route::get('/dashboard', CompanyDashboardLivewire::class)->name('vans.company.dashboard');
            Route::get('/orders', OrdersLivewire::class)->name('vans.company.orders');
            Route::get('/settings', GeneralSettingsLivewire::class)->name('vans.company.settings');
        });
    });
    /*
     *********************************************************************** 
     * Admin Routes
     ***********************************************************************
     */
    Route::prefix('admin')->middleware(['auth', 'auth.super.admin'])->group(function () {
        Route::get('/referralcodes', ReferralCodesLivewire::class)->name('admin.referralcodes');
        Route::get('/sellers/parent', ParentSellersLivewire::class)->name('admin.sellers.parent');
        Route::get('/sellers/child', ChildSellersLivewire::class)->name('admin.sellers.child');
        Route::get('/customers', CustomersLivewire::class)->name('admin.customers');

        Route::prefix('categories')->group(function () {
            Route::get('/', CategoriesLivewire::class)->name('admin.categories');
            Route::get('/delete', [CategoriesController::class, 'destroy'])->name('admin.categories.del');
        });

        Route::prefix('notifications')->controller(NotificationsController::class)->group(function () {
            Route::get('/', 'notificationsHome')->name('admin.notifications');
            Route::post('/send', 'notificationsSend')->name('admin.notifications.send');
        });

        Route::controller(AdminController::class)->group(function () {
            Route::get('/settings', 'settings')->name('admin.settings');
        });

        Route::controller(UserController::class)->group(function () {
            Route::get('delete/users', 'destroy')->name('admin.del.users');
        });

        Route::prefix('promocodes')->controller(PromoCodeController::class)->group(function () {
            Route::get('/', 'promocodesHome')->name('admin.promocodes');
            Route::post('/add', 'promocodesAdd')->name('admin.promocodes.add');
            Route::get('/delete', 'destroy')->name('admin.promocodes.del');
            Route::post('/{id}/update', 'promoCodesUpdate')->name('admin.promocodes.update');
        });

        Route::prefix('orders')->controller(OrderController::class)->group(function () {
            Route::get('/', OrdersLivewire::class)->name('admin.orders');
            Route::get('/van_inventory', VanInventoriesLivewire::class)->name('admin.orders.van.inventory');
            Route::get('/verified', 'adminOrdersVerified')->name('admin.orders.verified');
            Route::get('/unverified', 'adminOrdersUnverified')->name('admin.orders.unverified');
            Route::get('/complete', 'completeOrders')->name('admin.orders.complete');
            Route::get('/delete', 'adminOrdersDel')->name('admin.del.orders');

            Route::get('/van-inventory', OrdersLivewire::class)
                ->name('admin.order.van.inventory');
        });

        Route::controller(HomeController::class)->group(function () {
            Route::post('/update/pages', 'updatePages')->name('admin.update.pages');
        });
    });
    /*
     *********************************************************************** 
     * Stripe Routes
     ***********************************************************************
     */
    Route::prefix('stripe')->middleware(['auth'])->controller(StripeController::class)->group(function () {
        Route::get('requested_delivery/checkout_charge/{totalCharge}/{productName}', 'getCheckoutFormForRequestedDelivery')
            ->name('stripe.requested.delivery.checkout.form');
        Route::get('re_generate_connect_account_link', 'regenerateConnectAccountLink')
            ->withoutMiddleware('auth')
            ->name('stripe.regenerate.connect.account.link');
    });
    /*
     *********************************************************************** 
     * Stuart Routes
     ***********************************************************************
     */
    Route::prefix('stuart')->controller(StuartDeliveryController::class)->group(function () {
        Route::prefix('job')->group(function () {
            Route::post('/creation', 'stuartJobCreationForWeb')->name('stuart.job.creation');
        });
    });
});
