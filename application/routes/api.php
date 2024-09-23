<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RatesController;
use App\Http\Controllers\ConvertController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

    // список всех сообщений
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    Route::get('/', function (Request $request) {
        $method = $request->query('method');
        
        if ($method === 'rates') {
            return app(RatesController::class)->rates($request->query('currency'));
        }

        return response()->json([
            'status' => 'error',
            'code' => 404,
            'message' => 'Invalid method or request type'
        ], 404);
    });

    Route::post('/', function (Request $request) {
        $method = $request->query('method');

        if ($method === 'convert') {
            return app(ConvertController::class)->convert($request);
        }

        return response()->json([
            'status' => 'error',
            'code' => 404,
            'message' => 'Invalid method or request type'
        ], 404);
    });

});

