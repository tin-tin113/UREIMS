<?php

namespace App\Http\Controllers;

use App\Models\ExtensionActivity;
use App\Models\ExtensionBeneficiary;
use App\Models\ExtensionProgram;
use App\Models\ExtensionProject;
use App\Models\Campus;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $user  = auth()->user();
        $isAdmin = $user->isAdmin();

        // Scope queries: admins see everything, staff see only their own records
        $programQuery    = $isAdmin ? ExtensionProgram::query() : ExtensionProgram::where('created_by', $user->id);
        $projectQuery    = $isAdmin ? ExtensionProject::query()  : ExtensionProject::where('created_by', $user->id);
        $activityQuery   = $isAdmin ? ExtensionActivity::query() : ExtensionActivity::where('created_by', $user->id);
        $beneficiaryQuery = $isAdmin
            ? ExtensionBeneficiary::query()
            : ExtensionBeneficiary::whereIn('extension_project_id', ExtensionProject::where('created_by', $user->id)->pluck('id'));

        // Summary counts
        $totalPrograms    = (clone $programQuery)->count();
        $totalProjects    = (clone $projectQuery)->count();
        $totalActivities  = (clone $activityQuery)->count();
        $totalBeneficiaries = (clone $beneficiaryQuery)->count();

        // Beneficiary impact totals
        $beneficiaryMaleTotal   = (clone $beneficiaryQuery)->sum('male_count');
        $beneficiaryFemaleTotal = (clone $beneficiaryQuery)->sum('female_count');
        $beneficiaryHeadTotal   = (clone $beneficiaryQuery)->sum('total_count');

        // User and campus counts (visible only to admin, zero for staff)
        $totalUsers    = $isAdmin ? User::count() : 0;
        $totalCampuses = $isAdmin ? Campus::count() : 0;

        // Status breakdown — Programs
        $programsByStatus = [
            'draft'     => (clone $programQuery)->where('status', 'draft')->count(),
            'proposal'  => (clone $programQuery)->where('status', 'proposal')->count(),
            'ongoing'   => (clone $programQuery)->where('status', 'ongoing')->count(),
            'completed' => (clone $programQuery)->where('status', 'completed')->count(),
        ];

        // Status breakdown — Projects
        $projectsByStatus = [
            'draft'     => (clone $projectQuery)->where('status', 'draft')->count(),
            'proposal'  => (clone $projectQuery)->where('status', 'proposal')->count(),
            'ongoing'   => (clone $projectQuery)->where('status', 'ongoing')->count(),
            'completed' => (clone $projectQuery)->where('status', 'completed')->count(),
        ];

        // Status breakdown — Activities
        $activitiesByStatus = [
            'draft'     => (clone $activityQuery)->where('status', 'draft')->count(),
            'proposal'  => (clone $activityQuery)->where('status', 'proposal')->count(),
            'ongoing'   => (clone $activityQuery)->where('status', 'ongoing')->count(),
            'completed' => (clone $activityQuery)->where('status', 'completed')->count(),
        ];

        // Overdue items
        $overdueActivities = (clone $activityQuery)->where('status', '!=', 'completed')
            ->whereNotNull('target_date')
            ->where('target_date', '<', $today)
            ->with('project')
            ->latest('target_date')
            ->take(10)
            ->get();

        $overdueProjects = (clone $projectQuery)->where('status', '!=', 'completed')
            ->whereNotNull('target_end_date')
            ->where('target_end_date', '<', $today)
            ->latest('target_end_date')
            ->take(10)
            ->get();

        // Recent items
        $recentPrograms = (clone $programQuery)->with('campus')
            ->latest()
            ->take(5)
            ->get();

        $recentProjects = (clone $projectQuery)->with(['campus', 'program'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalPrograms',
            'totalProjects',
            'totalActivities',
            'totalBeneficiaries',
            'beneficiaryMaleTotal',
            'beneficiaryFemaleTotal',
            'beneficiaryHeadTotal',
            'totalUsers',
            'totalCampuses',
            'programsByStatus',
            'projectsByStatus',
            'activitiesByStatus',
            'overdueActivities',
            'overdueProjects',
            'recentPrograms',
            'recentProjects',
        ));
    }
}
