<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    $credentials = request()->validate([
        'username' => 'required|string',
        'password' => 'required',
    ]);

    if (auth()->attempt($credentials, request()->boolean('remember'))) {
        request()->session()->regenerate();
        return redirect()->intended('dashboard');
    }

    return back()->withErrors([
        'username' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
    ])->onlyInput('username');
})->middleware('guest');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');
