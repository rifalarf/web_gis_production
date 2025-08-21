<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MarkersApiController;

Route::get('/markers.geojson', MarkersApiController::class);
