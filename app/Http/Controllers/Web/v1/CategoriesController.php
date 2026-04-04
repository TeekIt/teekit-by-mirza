<?php

namespace App\Http\Controllers\Web\v1;

use App\Models\Categories;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function destroy(Request $request)
    {
        for ($i = 0; $i < count($request->categories); $i++) {
            Categories::findOrFail($request->categories[$i])->delete();
        }

        return response('Categories Deleted Successfully');
    }
}
