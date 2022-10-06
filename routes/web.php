<?php

use App\Http\Controllers\CustomLoginController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use LaravelWebauthn\Actions\PrepareAssertionData;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function (Request $request) {
    $webauthnKeys = $request->user()->webauthnKeys()
        ->get()
        ->map(function ($key) {
            return [
                'id' => $key->id,
                'name' => $key->name,
                'type' => $key->type,
                'last_active' => $key->updated_at->diffForHumans(),
            ];
        })
        ->toArray();

    return Inertia::render('Dashboard', [
        'webauthnKeys' => $webauthnKeys,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/auto/login', function () {
    auth()->login(\App\Models\User::get()->first());
    return redirect('/dashboard');
});

Route::prefix('webauthn')->group(function () {
    Route::get('/login', [CustomLoginController::class, 'index'])->name('webauthn.login');

    Route::get('/remember', [CustomLoginController::class, 'webAuthnRemember'])
        ->middleware('webauthn.remember');
});

require __DIR__ . '/auth.php';
