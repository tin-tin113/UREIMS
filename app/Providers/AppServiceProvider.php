<?php

namespace App\Providers;

use App\Models\ExtensionProgram;
use App\Models\ExtensionProject;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $phases = WorkflowService::PHASES;
                $programCounts = [];
                $projectCounts = [];
                foreach ($phases as $p) {
                    $programCounts[$p] = ExtensionProgram::where('status', $p)->count();
                    $projectCounts[$p] = ExtensionProject::where('status', $p)->count();
                }
                $view->with('sidebarProgramCounts', $programCounts);
                $view->with('sidebarProjectCounts', $projectCounts);
                $view->with('workflowPhases', $phases);
                $view->with('workflowLabels', WorkflowService::PHASE_LABELS);
            }
        });
    }
}
