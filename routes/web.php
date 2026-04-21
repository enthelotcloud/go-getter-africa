<?php

use Illuminate\Support\Facades\Route;


Route::view('/', 'home')->name('home');
Route::view('contact', 'contact')->name('contact');
Route::view('about', 'about')->name('about');
Route::view('faqs', 'faqs')->name('faqs');
Route::view('terms-of-service', 'terms')->name('terms');
Route::view('privacy-policy', 'privacy')->name('privacy');
Route::view('services', 'services')->name('services');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
