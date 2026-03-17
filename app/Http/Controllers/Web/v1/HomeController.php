<?php

namespace App\Http\Controllers\Web\v1;

use App\Models\Categories;
use App\Enums\DeliveryStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Pages;
use App\Models\Products;
use App\Models\User;
use App\Models\VerificationCodes;
use App\Models\WithdrawalRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Gate::allows('seller') || Gate::allows('child_seller')) {
            return redirect()->route('seller.dashboard');
        } else {
            return $this->adminHome();
        }
    }

    /**
     * Changes user setting provided in the parameter
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.0.0
     */
    public function changeSettings(Request $request)
    {
        User::where('id', '=', Auth::id())->update([
            'settings->'.$request->setting_name => $request->value,
        ]);

        return redirect()->route('home');
    }

    /**
     * Display's payment view
     *
     * @author Huzaifa Haleem
     *
     * @version 1.0.0
     */
    public function paymentSettings()
    {
        $payment_settings = User::find(Auth::id())->bank_details;

        return view('shopkeeper.settings.payment', compact('payment_settings'));
    }

    /**
     * Update's user password
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.0.0
     */
    public function adminPasswordUpdate(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);
        if ($validate->fails()) {
            return redirect()->back()->with('flash', flash('Password must be 8 characters long.')->error());
        }

        $user = User::find(Auth::id());
        if (Hash::check($request->old_password, $user->password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();

            return redirect()->back()->with('flash', flash('Your password has been updated successfully.')->success());
        } else {
            return redirect()->back()->with('flash', flash('Your old password is incorrect.')->error());
        }
    }

    /**
     * Update's payment settings
     *
     * @author Huzaifa Haleem
     *
     * @version 1.0.0
     */
    public function paymentSettingsUpdate(Request $request)
    {
        $data = $request->all();
        if (
            empty($data['bank']['two']['bank_name']) ||
            empty($data['bank']['two']['account_number']) ||
            empty($data['bank']['two']['branch'])
        ) {
            unset($data['bank']['two']);
        }

        unset($data['_token']);

        $data = $data['bank'];

        $user = User::find(Auth::id());
        $user->bank_details = json_encode($data);
        $user->save();

        flash('Bank Details Updated');

        return redirect()->back();
    }

    /**
     * Convert's CSV file to JSON
     *
     * @author Huzaifa Haleem
     *
     * @version 1.0.0
     */
    public function csvToJson($fname)
    {
        // open csv file
        if (! ($fp = fopen($fname, 'r'))) {
            exit("Can't open file...");
        }

        // read csv headers
        $key = fgetcsv($fp, '1024', ',');

        // parse csv rows into array
        $json = [];
        while ($row = fgetcsv($fp, '1024', ',')) {
            $json[] = array_combine($key, $row);
        }

        // release file handle
        fclose($fp);

        // encode array to json
        return json_encode($json);
    }

    /**
     * Return's admin home view
     *
     * @author Huzaifa Haleem
     *
     * @version 1.0.0
     */
    public function adminHome()
    {
        if (Gate::allows('superadmin')) {
            $pendingOrders = Orders::where('order_status', OrderStatusEnum::PENDING)->count();
            $totalProducts = Products::count();
            $totalOrders = Orders::where('payment_status', '!=', 'hidden')->count();
            $totalSales = Orders::where('payment_status', 'paid')->sum('current_total');

            return view('admin.home', compact(
                'pendingOrders',
                'totalProducts',
                'totalOrders',
                'totalSales'
            ));
        }

        abort(config('constants.HTTP_UNAUTHORIZED'));
    }

    public function updatePages(Request $request)
    {
        Pages::where('page_type', '=', 'terms')->update(['page_content' => $request->tos]);
        Pages::where('page_type', '=', 'help')->update(['page_content' => $request->help]);
        Pages::where('page_type', '=', 'faq')->update(['page_content' => $request->faq]);

        flash('Updated')->success();

        return Redirect::back();
    }

    /**
     * Render verified orders listing view for admin
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.0.0
     */
    public function adminOrdersVerified(Request $request)
    {
        $return_arr = [];
        $verified_orders = VerificationCodes::where('code->driver_failed_to_enter_code', '=', 'No')->orderByDesc('id');
        $verified_orders = $verified_orders->paginate(10);
        $orders_p = $verified_orders;
        foreach ($verified_orders as $order) {
            $order_details = Orders::where('id', '=', $order->order_id)->first();
            $items = OrderItems::where('order_id', '=', $order->order_id)->get();
            $item_arr = [];
            foreach ($items as $item) {
                $product = Products::getProductInfoWithRelations($order_details->seller_id, $item->product_id, ['*']);
                $item['product'] = $product;
                $item_arr[] = $item;
            }
            $order['order_details'] = $order_details;
            $order['items'] = $item_arr;
            $return_arr[] = $order;
        }
        $orders = $return_arr;

        return view('admin.verified_orders', compact('orders', 'orders_p'));
    }

    /**
     * Render unverified orders listing view for admin
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.0.0
     */
    public function adminOrdersUnverified(Request $request)
    {
        $return_arr = [];
        $verified_orders = VerificationCodes::query()
            ->where('code->driver_failed_to_enter_code', '=', 'Yes')
            ->orderByDesc('id');
        $verified_orders = $verified_orders->paginate(10);
        $orders_p = $verified_orders;
        foreach ($verified_orders as $order) {
            $order_details = Orders::query()->where('id', '=', $order->order_id)->first();
            $items = OrderItems::query()->where('order_id', '=', $order->order_id)->get();
            $item_arr = [];
            foreach ($items as $item) {
                $product = Products::getProductInfoWithRelations($order_details->seller_id, $item->product_id, ['*']);
                $item['product'] = $product;
                $item_arr[] = $item;
            }
            $order['order_details'] = $order_details;
            $order['items'] = $item_arr;
            $return_arr[] = $order;
        }
        $orders = $return_arr;

        return view('admin.unverified_orders', compact('orders', 'orders_p'));
    }

    /**
     * Delete selected orders
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.0.0
     */
    public function adminOrdersDel(Request $request)
    {
        for ($i = 0; $i < count($request->orders); $i++) {
            DB::table('orders')->where('id', '=', $request->orders[$i])->delete();
            DB::table('order_items')->where('order_id', '=', $request->orders[$i])->delete();
            DB::table('verification_codes')->where('order_id', '=', $request->orders[$i])->delete();
        }

        return response('Orders Deleted Successfully');
    }

    /**
     * It will show withdrawls to seller/admin
     * based on their auth id
     *
     * @version 1.0.0
     */
    public function withdrawals(): View
    {
        $transactions = WithdrawalRequests::getParentAndChildSellersWithdrawalRequests();

        return view('admin.withdrawal', compact('transactions'));
    }

    /**
     * It will show seller withdrawls requests
     *
     * @version 1.0.0
     */
    public function withdrawalsRequest(Request $request)
    {
        if (Gate::allows('superadmin')) {
            $with = WithdrawalRequests::find($request->id);
            $with->status = $request->status;
            $with->transaction_id = $request->t_id;
            $with->save();

            flash('Updated')->success();

            return Redirect::back();
        }
    }

    /**
     * It will show complete orders
     * based on the given criteria
     *
     * @version 1.0.0
     */
    public function completeOrders()
    {
        $orders = DB::table('orders')
            ->leftJoin('users', 'orders.created_by_id', '=', 'users.id')
            ->LeftJoin('drivers', 'orders.driver_id', '=', 'drivers.id')
            ->where('created_by_type', (new User)->getMorphClass())
            ->where('delivery_status', '=', DeliveryStatusEnum::COMPLETE)
            ->where('order_status', '=', OrderStatusEnum::COMPLETE)
            ->select(
                'drivers.f_name',
                'drivers.l_name',
                'orders.id',
                'orders.total_items',
                'orders.phone_number',
                'orders.house_no',
                'orders.address',
                'orders.type',
                'users.name'
            )
            ->paginate(10);

        return view('admin.complete-orders', compact('orders'));
    }

    /**
     * it will update the unverified orders to verified
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.0.0
     */
    public function clickToVerify($order_id)
    {
        VerificationCodes::where('order_id', $order_id)
            ->update(['code->driver_failed_to_enter_code' => 'No']);

        flash('Order Verified Successfully')->success();

        return redirect()->back();
    }
}
