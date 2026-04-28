<?php

namespace App\Http\Controllers\Web\v1;

use App\Enums\OrderStatusEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Pages;
use App\Models\Products;
use App\Models\User;
use App\Models\VerificationCodes;
use App\Models\WithdrawalRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
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
        if (in_array(User::getAuthUser()->role_id, [UserRoleEnum::SELLER->value, UserRoleEnum::CHILD_SELLER->value])) {
            return redirect()->route('seller.dashboard');
        } elseif (User::getAuthUser()->role_id === UserRoleEnum::COMPANY->value) {
            return redirect()->route('vans.company.dashboard');
        } elseif (User::getAuthUser()->role_id === UserRoleEnum::SUPERADMIN->value) {
            return $this->superAdminHome();
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
            'settings->' . $request->setting_name => $request->value,
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

        return view('seller.settings.payment', compact('payment_settings'));
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
    public function superAdminHome()
    {
        $pendingOrders = Orders::where('order_status', OrderStatusEnum::PENDING)->count();
        $totalOrders = Orders::where('payment_status', '!=', 'hidden')->count();
        $totalSales = Orders::where('payment_status', 'paid')->sum('current_total');
        $totalProducts = Products::count();

        return view('admin.home', compact(
            'pendingOrders',
            'totalProducts',
            'totalOrders',
            'totalSales'
        ));
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
