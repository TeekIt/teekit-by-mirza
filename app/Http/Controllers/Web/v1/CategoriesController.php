<?php

namespace App\Http\Controllers\Web\v1;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function destroy(Request $request)
    {
        for ($i = 0; $i < count($request->categories); $i++) {
            Categories::where('id', '=', $request->categories[$i])->delete();
        }

        return response('Categories Deleted Successfully');
    }
}
