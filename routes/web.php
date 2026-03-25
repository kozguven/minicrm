<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactInteractionController;
use App\Http\Controllers\CrmTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TodayController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/today');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class);
    Route::get('/today', TodayController::class);
    Route::get('/search/global', SearchController::class);
    Route::get('/reports/pipeline', [ReportController::class, 'pipeline']);
    Route::get('/reports/forecast', [ReportController::class, 'forecast']);
    Route::get('/reports/funnel', [ReportController::class, 'funnel']);
    Route::get('/reports/sales-cycle', [ReportController::class, 'salesCycle']);
    Route::get('/reports/performance', [ReportController::class, 'performance']);
    Route::get('/reports/data-quality', [ReportController::class, 'dataQuality']);

    Route::resource('roles', RoleController::class)
        ->except(['show', 'destroy']);

    Route::get('/companies', [CompanyController::class, 'index']);
    Route::get('/companies/create', [CompanyController::class, 'create']);
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit']);
    Route::get('/companies/{company}', [CompanyController::class, 'show']);
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::patch('/companies/{company}', [CompanyController::class, 'update']);

    Route::get('/contacts', [ContactController::class, 'index']);
    Route::get('/contacts/create', [ContactController::class, 'create']);
    Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit']);
    Route::get('/contacts/{contact}', [ContactController::class, 'show']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::patch('/contacts/{contact}', [ContactController::class, 'update']);
    Route::post('/contact-interactions', [ContactInteractionController::class, 'store']);
    Route::patch('/contact-interactions/{contactInteraction}/toggle-follow-up', [ContactInteractionController::class, 'toggleFollowUp']);

    Route::get('/opportunities', [OpportunityController::class, 'index']);
    Route::get('/opportunities/create', [OpportunityController::class, 'create']);
    Route::get('/opportunities/kanban', [OpportunityController::class, 'kanban']);
    Route::patch('/opportunities/bulk-stage', [OpportunityController::class, 'bulkStage']);
    Route::get('/opportunities/{opportunity}/edit', [OpportunityController::class, 'edit']);
    Route::get('/opportunities/{opportunity}', [OpportunityController::class, 'show']);
    Route::post('/opportunities', [OpportunityController::class, 'store']);
    Route::patch('/opportunities/{opportunity}', [OpportunityController::class, 'update']);
    Route::patch('/opportunities/{opportunity}/stage', [OpportunityController::class, 'updateStage']);
    Route::post('/opportunities/{opportunity}/convert', [DealController::class, 'convert']);

    Route::get('/deals', [DealController::class, 'index']);
    Route::get('/deals/create', [DealController::class, 'create']);
    Route::get('/deals/{deal}/edit', [DealController::class, 'edit']);
    Route::get('/deals/{deal}', [DealController::class, 'show']);
    Route::post('/deals', [DealController::class, 'store']);
    Route::patch('/deals/{deal}', [DealController::class, 'update']);

    Route::get('/tasks', [CrmTaskController::class, 'index']);
    Route::get('/tasks/create', [CrmTaskController::class, 'create']);
    Route::patch('/tasks/bulk', [CrmTaskController::class, 'bulkUpdate']);
    Route::get('/tasks/{crmTask}/edit', [CrmTaskController::class, 'edit']);
    Route::get('/tasks/{crmTask}', [CrmTaskController::class, 'show']);
    Route::post('/tasks', [CrmTaskController::class, 'store']);
    Route::patch('/tasks/{crmTask}', [CrmTaskController::class, 'update']);
    Route::patch('/tasks/{crmTask}/toggle-complete', [CrmTaskController::class, 'toggleComplete']);

    Route::get('/team', [TeamController::class, 'index']);
    Route::get('/team/create', [TeamController::class, 'create']);
    Route::post('/team', [TeamController::class, 'store']);

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});
