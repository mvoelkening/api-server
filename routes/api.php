<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**Route for login API */

Route::group(['middleware' => 'guest:attendee,exhibitor,presenter,organizer'], function () {
    Route::post('login', [AuthController::class, 'login']);
});
//get token
Route::post('token', [AuthController::class, 'auth']);

/**Midlleware for Auth Routes */
Route::middleware('auth:api')->group(function () {
    Route::resources([
        'user' => UserController::class,
    ]);
});
