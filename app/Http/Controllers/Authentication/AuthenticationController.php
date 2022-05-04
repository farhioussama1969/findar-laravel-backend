<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Factory;



class AuthenticationController extends Controller
{


    public function login(Request $request) {
        //$firebase = (new Factory)->withServiceAccount(__DIR__.'/firebase-config.json');

        //request data validation
        $request->validate([
            'phone' => 'required|max:9|min:9',
            'password' => 'required',
            'deviceName' => 'required',
            'fcmToken' => 'required',
        ]);

        //get user with phone
        $user = User::where('phone', $request->phone)->first();

        //check if user not null
        if(!is_null($user)) {
            //check the password
            if(Hash::check($request->password, $user->password)) {
                $credentials = $request->only('phone', 'password');
                if (Auth::attempt($credentials)) {
                    $authuser = auth()->user();
                    //delete all user access tokens
                    $user->tokens()->delete();
                    //update user fcm tokens
                    $user->fcm_token = $request->fcmToken;
                    $user->update();

                    return response()->json([ "success" => true,
                        "message" => "You have logged in successfully",
                        "accessToken" => $user->createToken($request->deviceName)->plainTextToken,
                        "userData" => $authuser]);
                }
            }else {
                return response()->json(["success" => false, "message" => "incorrect password"]);
            }
        }
        else{
            return response()->json(["success" => false, "message" => "phone does not exist"]);
        }
    }


    function logout(Request $request) {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json(["success" => true, "message" => "You have logout successfully"]);
    }
}
