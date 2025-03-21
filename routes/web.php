<?php

use App\Http\Controllers\NodeController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/view-project', [ProjectController::class, 'index'])->name('view-project');
Route::get('/detail-cpm/{id}', [NodeController::class, 'show'])->name('nodes.show');
Route::get('/update-cpm/{id}', [NodeController::class, 'showUpdate'])->name('nodes.showUpdate');

Route::post('/run-python', [NodeController::class, 'runPython']);
Route::post('/update-nodes', [NodeController::class, 'updateNodes']);
Route::post('/delete-node', [NodeController::class, 'deleteNode']);
Route::get('/create-project', [NodeController::class, 'index'])->name('create-project');
Route::post('/projects/store', [ProjectController::class, 'store'])->name('projects.store');
Route::get('/create-prompt/{id}', [ProjectController::class, 'createPrompt'])->name('tampil-prompt');
Route::post('/saveNodes', [ProjectController::class, 'saveNodes']);

Route::post('/update-total-price', [NodeController::class, 'updateTotalPrice'])
    ->name('updateTotalPrice');

Route::post('/predecessor/update', [NodeController::class, 'update'])->name('nodes.update');
Route::post('/predecessor/delete', [NodeController::class, 'delete'])->name('nodes.delete');

Route::post('/saveNodes', [NodeController::class, 'saveNodes'])->name('saveNodes');
    