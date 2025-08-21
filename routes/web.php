<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GeojsonController;
use App\Http\Controllers\RuasController;
use App\Http\Controllers\Api\MapApiController;
use App\Http\Controllers\KerusakanController;
use App\Http\Controllers\Api\KerusakanApiController;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->name('home');

Route::get('/api/segments.geojson', [MapApiController::class, 'index']);

Route::get('/ruas-jalan',          [RuasController::class,'index']);
Route::get('/ruas-jalan/{code}',   [RuasController::class,'show']);


Route::middleware('auth')->group(function () {

    /*  geojson upload wizard  */
    Route::get ('/ruas-jalan/upload/{mode}', [GeojsonController::class,'create'])
         ->whereIn('mode', ['insert','update'])          // only 2 values
         ->name('geojson.form');                         // ->route('geojson.form', 'insert')

    Route::post('/ruas-jalan/upload',        [GeojsonController::class,'upload'])
         ->name('geojson.upload');

    /*  existing delete  */
    Route::delete('/ruas-jalan/{code}',      [GeojsonController::class,'destroy'])
         ->name('ruas.destroy');
});




Route::get('/api/kerusakan.geojson', [KerusakanApiController::class,'index']);

Route::resource('kerusakan', KerusakanController::class)
     ->middleware(['auth'])     // create/edit/delete guarded
     ->except(['index','show']); // public can see

Route::get ('/kerusakan',        [KerusakanController::class,'index'])->name('kerusakan.index');
Route::get('/kerusakan/{kerusakan}', [KerusakanController::class,'show'])->name('kerusakan.show');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::get('/statistik', [App\Http\Controllers\StatistikController::class,'index'])
    ->name('statistik.index');


/*
Route::get('/names', [NameController::class, 'index'])->name('names.index');
Route::get('/names/{name}', [NameController::class, 'show'])->name('names.show');

Route::middleware('auth')->group(function () {
    Route::post('/names', [NameController::class, 'store'])->name('names.store');
    Route::get('/names/create', [NameController::class, 'create'])->name('names.create');
    Route::get('/names/{name}/edit', [NameController::class, 'edit'])->name('names.edit');
    Route::put('/names/{name}', [NameController::class, 'update'])->name('names.update');
    Route::delete('/names/{name}', [NameController::class, 'destroy'])->name('names.destroy');
});
*/
