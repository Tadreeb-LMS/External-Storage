<?php
Route::middleware(['web', 'auth'])->prefix('external-apps/external-storage')->group(function () {
    Route::get('/settings', 'Controllers\ExternalStorageController@index');
    Route::post('/settings', 'Controllers\ExternalStorageController@store');
    Route::post('/test-connection', 'Controllers\ExternalStorageController@testConnection');
});
?>
