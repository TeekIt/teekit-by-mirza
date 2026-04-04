<?php

namespace App\Http\Controllers\Web\v1;

use App\Http\Controllers\Controller;
use App\Models\Van;
use Illuminate\Http\Request;

class VanController extends Controller
{
    public function destroy(Request $request)
    {
        for ($i = 0; $i < count($request->vans); $i++) {
            Van::findOrFail($request->vans[$i])->delete();
        }

        return response('Vans Deleted Successfully');
    }
}
