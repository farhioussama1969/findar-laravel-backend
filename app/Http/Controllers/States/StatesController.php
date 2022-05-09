<?php

namespace App\Http\Controllers\States;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatesController extends Controller
{
    public function statesList(Request $request){
        $lang = $request->header('lang');
        $statesResponse = DB::table('states')->select('id', "name_{$lang} AS name", 'latitude', 'longitude')->orderBy('id')->paginate(48);
        return $statesResponse;
    }
}
