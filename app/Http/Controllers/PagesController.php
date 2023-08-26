<?php

namespace App\Http\Controllers;

use App\Pages;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * Fetches the page via given page type
     * @version 1.0.0
     */
    public function getPage(Request $request)
    {
        $page = Pages::query()->where('page_type', '=', $request->page_type)->get();
        return response()->json([
            'data' => $page,
            'status' => true,
            'message' => ''
        ], 200);
    }
}