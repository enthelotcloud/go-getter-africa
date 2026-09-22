<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;


Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

Route::view('/', 'home')->name('home');
Route::view('contact', 'contact')->name('contact');
Route::livewire('/portal', 'pages::nominee-portal')->name('nominee.portal');
Route::view('about', 'about')->name('about');
Route::view('faqs', 'faqs')->name('faqs');
Route::view('terms-of-service', 'terms')->name('terms');
Route::view('privacy-policy', 'privacy')->name('privacy');
Route::view('services', 'services')->name('services');
Route::livewire('projects','pages::projects')->name('projects');
Route::livewire('/projects/{project:slug}', 'pages::projects-show')->name('projects.show');

// Public Voting Pages
Route::livewire('/polls', 'pages::nomination-categories')->name('polls');
Route::livewire('/polls/{category:slug}', 'pages::nomination-category-single')->name('polls.category');
Route::livewire('/vote/{nomination:code}', 'pages::nomination-single')->name('polls.vote');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // Admins Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::livewire('/dashboard','admin::dashboard')->name('dashboard');

        Route::livewire('/categories','admin::nomination-categories')->name('nomination-categories');

        Route::livewire('/nominations','admin::nominations')->name('nominations');

        Route::livewire('/projects', 'admin.project-manager')->name('projects');

        Route::livewire('/token-packages', 'admin::token-packages')->name('token-packages');

        Route::livewire('/wallets', 'admin::wallets')->name('wallets');

        Route::livewire('/payouts', 'admin::payouts')->name('payouts');

    });

    // Staffs Routes
    Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
        Route::livewire('/dashboard','staff::dashboard')->name('dashboard');
    });

    // Voters Routes
    Route::middleware(['role:voter'])->prefix('voter')->name('voter.')->group(function () {
        Route::livewire('/dashboard','voter::dashboard')->name('dashboard');
    });

    // Nominees Routes
    Route::middleware(['role:nominee'])->prefix('nominee')->name('nominee.')->group(function () {
        Route::livewire('/dashboard','nominee::dashboard')->name('dashboard');
    });
});


require __DIR__.'/settings.php';
