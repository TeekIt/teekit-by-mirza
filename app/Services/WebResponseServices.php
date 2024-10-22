<?php

namespace App\Services;

final class WebResponseServices
{
    public static function getWebResponse($status, $message)
    {
        ($status) ? flash($message)->success() : flash($message)->error();
    }
    
    public static function getValidationResponseRedirectBack($errors)
    {
        return redirect()->back()->withErrors($errors)->withInput();
    }

    public static function getResponseRedirectBack($status, $message)
    {
        return redirect()->back()->with($status, $message);
    }
}
