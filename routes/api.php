<?php

use Illuminate\Support\Facades\Route;

Route::post('geocode', \Gabelbart\Laravel\Nova\Fields\Geolocation\Http\GeocodingController::class);
