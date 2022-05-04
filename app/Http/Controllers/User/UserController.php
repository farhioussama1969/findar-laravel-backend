<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class UserController extends Controller
{


    public function auth(Request $request){
        $accessToken = $request->bearerToken();
        $user = DB::table('users')->where('access_token', $accessToken)->get();
        return isNull($user);
//        if(isEmpty($user)){
//            return Response::json(array(
//                'code'      =>  401,
//                'message'   =>  'Unauthorized',
//                'user' => $user
//            ), 401);
//        }
//        else{
//            $userId = $user->id;
//            return true;
//        }
    }
}
