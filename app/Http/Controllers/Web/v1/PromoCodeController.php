<?php

namespace App\Http\Controllers\Web\v1;

use App\Enums\OrderByEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\User;
use App\Rules\Seller\IsParentOrChildSellerId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PromoCodeController extends Controller
{
    /**
     * Returns promo codes form & list view
     *
     * @version 1.0.0
     */
    public function promocodesHome()
    {
        /* Get stores names for select dropdown */
        $stores = User::getParentAndChildSellers(columns: ['id', 'business_name']);
        $promoCodes = PromoCode::getAll('desc');

        return view('admin.promo_codes', compact('promoCodes', 'stores'));
    }

    /**
     * Deletes the specific promo code via ajax call
     *
     * @version 1.0.0
     */
    public function destroy(Request $request)
    {
        if (User::getAuthUser()->role_id == UserRoleEnum::SUPERADMIN->value) {
            for ($i = 0; $i < count($request->promocodes); $i++) {
                PromoCode::where('id', '=', $request->promocodes[$i])->forceDelete();
            }

            return response('Promocodes Deleted Successfully');
        }
    }

    /**
     * Adds promo code into the database
     *
     * @version 1.0.0
     */
    public function promocodesAdd(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'promo_code' => 'required|string|unique:promo_codes|max:20',
                'discount_type' => 'required',
                'discount' => 'required|int',
                'order_number' => 'nullable|int',
                'usage_limit' => 'nullable|int',
                'min_amnt_for_discount' => 'required|int',
                'max_amnt_for_discount' => 'required|int',
                'store_id' => ['nullable', new IsParentOrChildSellerId],
                'free_delivery' => 'nullable|string',
                'expiry_dt' => 'required',
            ]);
            if ($validatedData->fails()) {
                session()->flash('error', $validatedData->errors());

                return Redirect::back()->withInput($request->input());
            }

            $validatedData = $validatedData->validated();

            PromoCode::addOrUpdate($validatedData);

            session()->flash('success', 'Promo code saved successfully.');

            return back();
        } catch (Throwable $error) {
            report($error);
            session()->flash('error', $error->getMessage());

            return back();
        }
    }

    /**
     * Updates the specific promo code via popup modal
     *
     * @version 1.0.0
     */
    public function promoCodesUpdate(Request $request, $id)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'promo_code' => 'required|string|max:20',
                'discount_type' => 'required',
                'discount' => 'required|int',
                'order_number' => 'nullable|int',
                'usage_limit' => 'nullable|int',
                'min_amnt_for_discount' => 'required|int',
                'max_amnt_for_discount' => 'required|int',
                'store_id' => ['nullable', new IsParentOrChildSellerId],
                'free_delivery' => 'nullable|string',
                'expiry_dt' => 'required|date',
            ]);
            if ($validatedData->fails()) {
                session()->flash('error', $validatedData->errors());

                return Redirect::back()->withInput($request->input());
            }

            $validatedData = $validatedData->validated();

            PromoCode::addOrUpdate($validatedData, $id);

            session()->flash('success', 'Promo code updated successfully.');

            return back();
        } catch (Throwable $error) {
            report($error);
            session()->flash('error', $error->getMessage());

            return back();
        }
    }
}
