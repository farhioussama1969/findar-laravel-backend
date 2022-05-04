<?php

namespace App\Http\Controllers\States;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatesController extends Controller
{
    public function states(Request $request){
        $statesList = DB::table('states')->select('id', 'name_ar', 'name_en')->paginate();
        //return $statesList;
        return $request->input('phone');
        //return $request->bearerToken();
    }
}
