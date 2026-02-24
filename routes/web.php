<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExtensionProgramController;
use App\Http\Controllers\ExtensionProjectController;
use App\Http\Controllers\ExtensionActivityController;
use App\Http\Controllers\ExtensionBeneficiaryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes (require authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Extension Programs
    Route::resource('extension/programs', ExtensionProgramController::class)
        ->names('extension.programs');

    // Extension Projects
    Route::resource('extension/projects', ExtensionProjectController::class)
        ->names('extension.projects');

    // Extension Activities
    Route::resource('extension/activities', ExtensionActivityController::class)
        ->names('extension.activities')
        ->except(['show']);

    // Extension Beneficiaries (no show view — managed inline on project)
    Route::resource('extension/beneficiaries', ExtensionBeneficiaryController::class)
        ->names('extension.beneficiaries')
        ->except(['show']);

    // Workflow status transitions & documents
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::post('{type}/{id}/advance',         [WorkflowController::class, 'advance'])->name('advance');
        Route::post('{type}/{id}/bypass',           [WorkflowController::class, 'bypass'])->name('bypass');
        Route::post('{type}/{id}/upload-document',  [WorkflowController::class, 'uploadDocument'])->name('upload-document');
        Route::delete('document/{document}',        [WorkflowController::class, 'deleteDocument'])->name('delete-document');
    });

    // Admin: User Management
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    });
});
