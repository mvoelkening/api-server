<?php

namespace App\Helpers;

use App\Models\Exhibitor;
use App\Models\TrackLogin;
use App\Models\VirtualLogin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoginHelper
{

    public static function saveLogin($type, $code, $client, $event)
    {
        VirtualLogin::where('login_code', $code)
            ->where('login_client', $client)
            ->where('login_event', $event)
            ->whereNull('login_time_logout')
            ->update([
                'login_time_logout' => NOW()
            ]);


        $inserted_id = 0;
        $new_virtual_login = new VirtualLogin();
        $new_virtual_login->login_code = $code;
        $new_virtual_login->login_type = $type;
        $new_virtual_login->login_client = $client;
        $new_virtual_login->login_event = $event;
        $new_virtual_login->login_time_login = NOW();
        if ($new_virtual_login->save()) {
            $inserted_id = $new_virtual_login->ID;
        }

        return $inserted_id;
    }

    public static function saveFailedLogin($email, $code, $server)
    {
        $track_login = new TrackLogin();
        $track_login->email_value = $email;
        $track_login->code_value = $code;
        $track_login->server_value = $server;
        $track_login->save();
    }
}
