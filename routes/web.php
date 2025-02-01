<?php

use App\Http\Controllers\NodeController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/view-project', [ProjectController::class, 'index'])->name('view-project');

Route::get('/detail-cpm', function () {
    return view('detail-cpm');
});
// Route::get('/detail-cpm/{id}', [NodeController::class, 'show'])->name('detail-cpm');
Route::get('/detail-cpm/{id}', [NodeController::class, 'show'])->name('nodes.show');
Route::post('/run-python', [NodeController::class, 'runPython']);
Route::post('/update-nodes', [NodeController::class, 'updateNodes']);



