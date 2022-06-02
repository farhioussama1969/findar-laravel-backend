<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{



    public function privacyPolicy(){

        $privacyPolicy = DB::table('settings')->select('privacy_policy')->first();

        return $privacyPolicy->privacy_policy;
    }
}
