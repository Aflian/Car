<?php

use App\Http\Controllers\pay;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('payment',[pay::class,'index']);
Route::get('/',[HomeController::class,'index']);