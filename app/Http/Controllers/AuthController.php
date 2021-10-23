<?php

namespace App\Http\Controllers;

use App\Helpers\LoginHelper;
use App\Http\Controllers\Controller;
use App\Models\Exhibitor;
use App\Models\Instructor;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $firstStr = substr($request->code, 0, 1);
            if ($firstStr == 'X') { //exhibitor
                \Config::set('user_type', 'X');

                $credentials = ['exhibitor_contact_email' => $request->email, 'exhibitor_code' => strtoupper($request->code)];

                $user = Exhibitor::where('exhibitor_contact_email', $credentials['exhibitor_contact_email'])->where('exhibitor_code', $credentials['exhibitor_code'])->first();
                if ($user) {
                    if ($token = auth('exhibitor')->attempt($credentials)) {

                        $vl_id = LoginHelper::saveLogin('exhibitor', $user->exhibitor_code, $user->exhibitor_client, $user->exhibitor_event);
                        return response()->json([
                            'token' => $token,
                            'type' => 'bearer', // you can ommit this
                            'expires' => auth('exhibitor')->factory()->getTTL() * 72000, // time to expiration,
                            'user_type' => 'X',
                            'vl_id' => $vl_id
                        ]);
                    }
                    LoginHelper::saveFailedLogin($request->email, strtoupper($request->code), $_SERVER['HTTP_REFERER']);
                    return response()->json(['error' => 'Unauthorized'], 401);
                } else {
                    LoginHelper::saveFailedLogin($request->email, strtoupper($request->code), $_SERVER['HTTP_REFERER']);
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            } else if ($firstStr == 'I') { //presenter
                \Config::set('user_type', 'I');


                $credentials = ['instructor_email' => $request->email, 'instructor_code' => strtoupper($request->code)];

                $user = Instructor::where('instructor_email', $credentials['instructor_email'])->where('instructor_code', $credentials['instructor_code'])->first();
                if ($user) {
                    if ($token = auth('presenter')->attempt($credentials)) {
                        $vl_id = LoginHelper::saveLogin('presenter', $user->instructor_code, $user->instructor_client, $user->instructor_event);
                        return response()->json([
                            'token' => $token,
                            'type' => 'bearer', // you can ommit this
                            'expires' => auth('presenter')->factory()->getTTL() * 72000, // time to expiration,
                            'user_type' => 'I',
                            'vl_id' => $vl_id
                        ]);
                    }
                    LoginHelper::saveFailedLogin($request->email, strtoupper($request->code), $_SERVER['HTTP_REFERER']);
                    return response()->json(['error' => 'Unauthorized'], 401);
                } else {
                    LoginHelper::saveFailedLogin($request->email, strtoupper($request->code), $_SERVER['HTTP_REFERER']);
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            } else if ($firstStr == 'R') { //attendee
                $credentials = ['registrations_email' => $request->email, 'registrations_code' => strtoupper($request->code)];
                \Config::set('user_type', 'R');

                $user = Registration::where('registrations_email', $credentials['registrations_email'])->where('registrations_code', $credentials['registrations_code'])->first();

                if ($user) {
                    if ($user->registrations_status == 'D') {
                        return response()->json(['error' => 'Deleted'], 200);
                    }
                    if ($token = auth('attendee')->attempt($credentials)) {

                        $vl_id = LoginHelper::saveLogin('attendee', $user->registrations_code, $user->registrations_client, $user->registrations_event);
                        return response()->json([
                            'token' => $token,
                            'type' => 'bearer', // you can ommit this
                            'expires' => auth('attendee')->factory()->getTTL() * 1, // time to expiration
                            'user_type' => 'R',
                            'vl_id' => $vl_id
                        ]);
                    }
                    LoginHelper::saveFailedLogin($request->email, strtoupper($request->code), $_SERVER['HTTP_REFERER']);
                    return response()->json(['error' => 'Unauthorized'], 401);
                } else {
                    LoginHelper::saveFailedLogin($request->email, strtoupper($request->code), $_SERVER['HTTP_REFERER']);
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            } else if ($firstStr == 'U') { //event organizer
                $credentials = ['user_email' => $request->email, 'user_code' => strtoupper($request->code)];
                \Config::set('user_type', 'U');
                $user = User::where('user_email', $credentials['user_email'])->where('user_code', $credentials['user_code'])->first();
                if ($user) {
                    if ($user->user_status == 'D') {
                        return response()->json(['error' => 'Deleted'], 200);
                    }
                    if ($token = auth('organizer')->attempt($credentials)) {
                        if ($user->user_administrator == 'Y') {
                            return response()->json([
                                'token' => $token,
                                'type' => 'bearer', // you can ommit this
                                'expires' => auth('organizer')->factory()->getTTL() * 72000, // time to expiration
                                'user_type' => 'A'
                            ]);
                        } else {
                            return response()->json([
                                'token' => $token,
                                'type' => 'bearer', // you can ommit this
                                'expires' => auth('organizer')->factory()->getTTL() * 72000, // time to expiration
                                'user_type' => 'U'
                            ]);
                        }
                    }
                    LoginHelper::saveFailedLogin($request->email, strtoupper($request->code), $_SERVER['HTTP_REFERER']);
                    return response()->json(['error' => 'Unauthorized'], 401);
                } else {
                    LoginHelper::saveFailedLogin($request->email, strtoupper($request->code), $_SERVER['HTTP_REFERER']);
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            } else {
                LoginHelper::saveFailedLogin($request->email, strtoupper($request->code), $_SERVER['HTTP_REFERER']);
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (\Throwable $e) {
            Log::error($e);
            LoginHelper::saveFailedLogin($request->email, strtoupper($request->code), $_SERVER['HTTP_REFERER']);
            return response()->json(['error' => 'Unknown error!'], 500);
        }
    }
}
