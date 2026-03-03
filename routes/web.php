<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExtensionProgramController;
use App\Http\Controllers\ExtensionProjectController;
use App\Http\Controllers\ExtensionActivityController;
use App\Http\Controllers\ExtensionBeneficiaryController;
use App\Http\Controllers\ExtensionBudgetItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\ProposalWizardController;
use App\Http\Controllers\EvaluationFormController;
use App\Http\Controllers\PublicEvaluationController;
use App\Http\Controllers\EvaluationEncodeController;

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
| Public Evaluation Routes (no auth required — respondents via link / QR)
|--------------------------------------------------------------------------
*/

Route::prefix('evaluate')->name('evaluation.public.')->group(function () {
    Route::get('{token}',       [PublicEvaluationController::class, 'show'])->name('show');
    Route::post('{token}',      [PublicEvaluationController::class, 'submit'])->name('submit');
    Route::get('{token}/thanks', [PublicEvaluationController::class, 'thanks'])->name('thanks');
});

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

    // Extension Beneficiaries (nested under projects)
    Route::prefix('extension/projects/{project}/beneficiaries')->name('extension.beneficiaries.')->group(function () {
        Route::get('/', [ExtensionBeneficiaryController::class, 'index'])->name('index');
        Route::get('create', [ExtensionBeneficiaryController::class, 'create'])->name('create');
        Route::post('/', [ExtensionBeneficiaryController::class, 'store'])->name('store');
        Route::get('{beneficiary}/edit', [ExtensionBeneficiaryController::class, 'edit'])->name('edit');
        Route::patch('{beneficiary}', [ExtensionBeneficiaryController::class, 'update'])->name('update');
        Route::delete('{beneficiary}', [ExtensionBeneficiaryController::class, 'destroy'])->name('destroy');
    });

    // Extension Budget Items (nested under projects)
    Route::prefix('extension/projects/{project}/budget-items')->name('extension.budget-items.')->group(function () {
        Route::get('/', [ExtensionBudgetItemController::class, 'index'])->name('index');
        Route::post('/', [ExtensionBudgetItemController::class, 'store'])->name('store');
        Route::patch('{budgetItem}', [ExtensionBudgetItemController::class, 'update'])->name('update');
        Route::delete('{budgetItem}', [ExtensionBudgetItemController::class, 'destroy'])->name('destroy');
    });

    // Workflow status transitions & documents
    Route::prefix('workflow')->name('workflow.')->group(function () {
        // Phase advancement
        Route::post('{type}/{id}/advance',         [WorkflowController::class, 'advance'])->name('advance');
        Route::post('{type}/{id}/bypass',           [WorkflowController::class, 'bypass'])->name('bypass');

        // Document management
        Route::post('{type}/{id}/upload-document',  [WorkflowController::class, 'uploadDocument'])->name('upload-document');
        Route::patch('document/{document}/type',    [WorkflowController::class, 'updateDocumentType'])->name('update-document-type');
        Route::delete('document/{document}',        [WorkflowController::class, 'deleteDocument'])->name('delete-document');
    });

    // Proposal Submission Wizard (program / project)
    Route::prefix('proposal/{type}')->name('proposal.wizard.')->group(function () {
        // Step 1 — Start
        Route::get('start',                 [ProposalWizardController::class, 'start'])->name('start');
        Route::post('start',                [ProposalWizardController::class, 'saveStart'])->name('save-start');

        // Step 2 — Upload Documents
        Route::get('upload',                [ProposalWizardController::class, 'upload'])->name('upload');
        Route::post('upload',               [ProposalWizardController::class, 'saveUpload'])->name('save-upload');
        Route::post('upload/remove',        [ProposalWizardController::class, 'removeFile'])->name('remove-file');
        Route::post('upload/update-label',  [ProposalWizardController::class, 'updateFileLabel'])->name('update-file-label');
        Route::post('upload/continue',      [ProposalWizardController::class, 'saveUploadContinue'])->name('save-upload-continue');

        // Step 3 — Enter Details / Metadata
        Route::get('details',               [ProposalWizardController::class, 'details'])->name('details');
        Route::post('details',              [ProposalWizardController::class, 'saveDetails'])->name('save-details');

        // Step 4 (program only) — Add Projects
        Route::get('projects',              [ProposalWizardController::class, 'projects'])->name('projects');
        Route::post('projects',             [ProposalWizardController::class, 'saveProjects'])->name('save-projects');

        // Confirmation
        Route::get('confirmation',          [ProposalWizardController::class, 'confirmation'])->name('confirmation');

        // Submit & Next Steps
        Route::post('submit',               [ProposalWizardController::class, 'submit'])->name('submit');
        Route::get('next-steps',            [ProposalWizardController::class, 'nextSteps'])->name('next-steps');

        // Save Draft (from any step) — persists to DB
        Route::post('save-draft',           [ProposalWizardController::class, 'saveDraft'])->name('save-draft');

        // Continue Draft — load from DB into session
        Route::get('continue/{id}',         [ProposalWizardController::class, 'continueDraft'])->name('continue-draft');

        // Delete Draft
        Route::delete('draft/{id}',         [ProposalWizardController::class, 'deleteDraft'])->name('delete-draft');

        // Cancel
        Route::post('cancel',               [ProposalWizardController::class, 'cancel'])->name('cancel');
    });

    // Admin: User Management (admin only)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    });

    // Evaluation Module
    Route::prefix('evaluation')->name('evaluation.')->group(function () {
        // Form management (admin & staff can manage their own)
        Route::resource('forms', EvaluationFormController::class)->names('forms');
        Route::patch('forms/{form}/toggle-active', [EvaluationFormController::class, 'toggleActive'])->name('forms.toggle-active');
        Route::get('forms/{form}/results', [EvaluationFormController::class, 'results'])->name('forms.results');
        Route::post('forms/{form}/duplicate', [EvaluationFormController::class, 'duplicate'])->name('forms.duplicate');

        // AJAX: form criteria (for template loading) & projects for a given program
        Route::get('forms/{form}/criteria', [EvaluationFormController::class, 'formCriteria'])->name('forms.criteria');
        Route::get('projects-by-program/{program}', [EvaluationFormController::class, 'projectsByProgram'])->name('projects-by-program');

        // Staff encoding (hardcopy entry)
        Route::get('encode', [EvaluationEncodeController::class, 'create'])->name('encode.create');
        Route::post('encode', [EvaluationEncodeController::class, 'store'])->name('encode.store');
    });
});
