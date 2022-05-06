<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Auth as fireAuth;



class AuthenticationController extends Controller
{



    public function phoneCheck(Request $request){
        $request->validate([
            'phone' => 'required|max:9|min:9',
        ]);
        //get user with phone
        $user = User::where('phone', $request->phone)->first();
        //check if user exist or not
        if(!is_null($user)) {
            return response()->json(["success" => true, "message" => "phone exist"]);
        }
        else{
            return response()->json(["success" => false, "message" => "phone does not exist"]);
        }
    }


    public function login(Request $request) {
        $request->validate([
            'phone' => 'required|max:9|min:9',
            'password' => 'required',
            'deviceName' => 'required',
            'fcmToken' => 'required',
        ]);
        $user = User::where('phone', $request->phone)->first();
        if(!is_null($user)) {
            if(Hash::check($request->password, $user->password)) {
                $credentials = $request->only('phone', 'password');
                if (Auth::attempt($credentials)) {
                    $authuser = auth()->user();
                    $user->tokens()->delete();
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


    public function register(Request $request){

        $request->validate([
            'fullName' => 'required|min:6',
            'phone' => 'required|max:9|min:9',
            'password' => 'required|min:8',
            'deviceName' => 'required',
            'fcmToken' => 'required',
        ]);

        $userIfExist = User::where('phone', $request->phone)->first();
        if(!is_null($userIfExist)) {
            return response()->json(["success" => false, "message" => "phone already exist"]);
        }
        else{
            $factory = (new Factory)->withServiceAccount(__DIR__.'/firebase-config.json');
            try {
                $auth = $factory->createAuth();
                $firebaseUser = $auth->getUserByPhoneNumber('+213' . $request->phone);
                $user = User::create(['name'=> $request->fullName, 'phone'=> $request->phone, 'fcm_token'=> $request->fcmToken, 'password' => Hash::make($request->password)]);
                return response()->json([ "success" => true,
                    "message" => "account created successfully",
                    "accessToken" => $user->createToken($request->deviceName)->plainTextToken,
                    "userData" => $user]);
            } catch (UserNotFound $e) {
                return response()->json(["success" => false, "message" => "phone no confirmed"]);
                echo $e->getMessage();
            }
        }


    }


    function logout(Request $request) {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json(["success" => true, "message" => "You have logout successfully"]);
    }
}
