<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class UserController extends Controller
{


    public function statistic(Request $request){
        $user = request()->user();

        $totalAdvertisements= DB::table('advertisements')->where('user_id', '=', "{$user->id}")->count('*');
        $totalViews= DB::table('views')->whereIn('advertisement_id', function($query) use ($user) {
            $query->select('id')->from('advertisements')->where('user_id' , '=', "{$user->id}");
        })->count('*');
        $reviews= DB::table('advertisements')->select(DB::raw("(SELECT ROUND(SUM(value)/COUNT(*), 1) FROM reviews WHERE advertisement_id = advertisements.id) AS reviews"),)->where('user_id', '=', "{$user->id}")->first();

        return response()->json(["totalAdvertisements" => $totalAdvertisements,
                                 "totalViews" => $totalViews,
                                 "reviews" => $reviews->reviews ?? '0',]);
    }


    public function updateInformations(Request $request){

        $user = request()->user();

        $request->validate([
            'fullName' => 'required|min:6',
        ]);

        $user->name = $request->fullName;
        $user->update();

        return response()->json(["success" => true,
            "message" => "full name updated successfully",
            "user"=> $user
            ]);
    }


    public function changePassword(Request $request){

        $user = request()->user();

        $request->validate([
            'oldPassword' => 'required|min:8',
            'newPassword' => 'required|min:8',
        ]);

        if(Hash::check($request->oldPassword, $user->password)) {
            $user->password = Hash::make($request->newPassword);
            $user->update();
            return response()->json(["success" => true,
                "message" => "password updated successfully",
            ]);
        }else{
            return response()->json(["success" => false,
                "message" => "your old password is wrong",
            ]);
        }


    }
}
