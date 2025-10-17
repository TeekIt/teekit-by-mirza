<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Pages;
use App\Services\JsonResponseServices;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * Fetches the page via given page type
     *
     * @version 1.0.0
     */
    public function getPage(Request $request)
    {
        $page = Pages::where('page_type', '=', $request->page_type)->get();

        return JsonResponseServices::getApiResponse(
            $page,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
}
