<?php

use Illuminate\Support\Facades\Route;
use Spatie\LaravelIgnition\Http\Controllers\UpdateConfigController;

Route::post('_ignition/update-config', UpdateConfigController::class)->name('updateConfig');
