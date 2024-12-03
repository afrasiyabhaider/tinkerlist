<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\PartController;

Route::apiResource('episodes', EpisodeController::class);
Route::prefix('episode/{episode}')->group(function () {
    Route::get('/parts', [PartController::class, 'listParts'])->name('episode.parts.list');
    Route::post('/part', [PartController::class, 'addPart'])->name('episode.parts.store');
    Route::delete('/parts/{part}', [PartController::class, 'deletePart'])->name('episode.parts.destroy');
    Route::post('/parts/update/positions', [PartController::class, 'updatePartsPositions'])->name('episode.parts.updatePositions');
    Route::post('/parts/reorder', [PartController::class, 'reorderParts'])->name('episode.parts.reorder');
});
