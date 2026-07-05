<?php

use App\Http\Controllers\EditorVideoIA\EditorVideoController;
use Illuminate\Support\Facades\Route;

Route::get('/editor-video', [EditorVideoController::class, 'index'])->name('editor-video.index');
Route::post('/editor-video/media/upload', [EditorVideoController::class, 'upload'])->name('editor-video.media.upload');
Route::delete('/editor-video/media/{mediaAsset}', [EditorVideoController::class, 'deleteMedia'])->name('editor-video.media.delete');
Route::get('/editor-video/project/load', [EditorVideoController::class, 'loadProject'])->name('editor-video.project.load');
Route::post('/editor-video/project/save', [EditorVideoController::class, 'saveProject'])->name('editor-video.project.save');
