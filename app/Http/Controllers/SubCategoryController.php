<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function destroy(Request $request)
    {
        for ($i = 0; $i < count($request->subCategories); $i++) {
            SubCategory::where('id', '=', $request->subCategories[$i])->delete();
        }

        return response("Subcategories Deleted Successfully");
    }
}
