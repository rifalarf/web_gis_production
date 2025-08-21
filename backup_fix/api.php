<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KerusakanApiController;

Route::get('/kerusakan.geojson', KerusakanApiController::class);
