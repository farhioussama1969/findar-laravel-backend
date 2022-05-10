<?php

namespace App\Http\Controllers\Favorites;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoritesController extends Controller
{
    public function addToFavorites(Request $request){
        $request->validate([
            'advertisementId' => 'required|integer',
        ]);

        $user = request()->user();
        $advertisementId = $request->advertisementId;

        $checkIfFavoriteExist = DB::table('favorites')->select('*')->where('user_id','=' ,"{$user->id}")->where('advertisement_id','=' ,"{$advertisementId}")->first();

        if(is_null($checkIfFavoriteExist)){
            DB::table('favorites')->insert([
                'user_id' => $user->id,
                'advertisement_id' => $advertisementId,
                'created_at' => now()
            ]);
            return response()->json(["success" => true, "message" => "Successfully added to favorite"]);
        }
        else{
            return response()->json(["success" => false, "message" => "Already added to favorite"]);
        }
    }

    public function deleteFromFavorites(Request $request){
        $request->validate([
            'advertisementId' => 'required|integer',
        ]);

        $user = request()->user();
        $advertisementId = $request->advertisementId;

        $checkIfFavoriteExist = DB::table('favorites')->select('*')->where('user_id','=' ,"{$user->id}")->where('advertisement_id','=' ,"{$advertisementId}")->first();

        if(!is_null($checkIfFavoriteExist)){
            DB::table('favorites')->where('user_id','=' ,"{$user->id}")->where('advertisement_id','=' ,"{$advertisementId}")->delete();
            return response()->json(["success" => true, "message" => "Successfully deleted from favorite"]);
        }
        else{
            return response()->json(["success" => false, "message" => "Item not found in favorite"]);
        }
    }
}
