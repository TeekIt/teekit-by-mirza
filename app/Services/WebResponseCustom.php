<?php

namespace App\Services;

final class WebResponseCustom
{
    public static function getValidationResponseRedirectBack($errors)
    {
        return redirect()->back()->withErrors($errors)->withInput();
    }

    public static function getResponseRedirectBack($status, $message)
    {
        return redirect()->back()->with($status, $message);
    }
}
