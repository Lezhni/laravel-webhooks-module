<?php

Route::group(['middleware' => 'api', 'prefix' => 'api'], function () {
    Route::post('webhook/{service}', \Modules\OuterServices\Http\Controllers\OuterServicesController::class);
});
