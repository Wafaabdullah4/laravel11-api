<?php

use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// menambahkan route post disini

Route::apiResource('/posts', App\Http\Controllers\Api\PostController::class);