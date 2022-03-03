<?php

namespace App\Http\Controllers\v1\Rest;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Webview;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function gameUrls() {
        return [
            'android' => Setting::where('key', 'game_url_android')->first()->value ?? null,
            'ios' => Setting::where('key', 'game_url_ios')->first()->value ?? null
        ];
    }
    public function webviews() {
        return self::Response(200,Webview::all());
    }

}
