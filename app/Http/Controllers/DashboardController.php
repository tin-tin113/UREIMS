<?php

namespace App\Http\Controllers;

use App\Models\ExtensionActivity;
use App\Models\ExtensionBeneficiary;
use App\Models\ExtensionProgram;
use App\Models\ExtensionProject;
use App\Models\Campus;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Summary counts
        $totalPrograms    = ExtensionProgram::count();
        $totalProjects    = ExtensionProject::count();
        $totalActivities  = ExtensionActivity::count();
        $totalBeneficiaries = ExtensionBeneficiary::count();

        // Beneficiary impact totals
        $beneficiaryMaleTotal   = ExtensionBeneficiary::sum('male_count');
        $beneficiaryFemaleTotal = ExtensionBeneficiary::sum('female_count');
        $beneficiaryHeadTotal   = ExtensionBeneficiary::sum('total_count');

        // User and campus counts (for admin)
        $totalUsers    = User::count();
        $totalCampuses = Campus::count();

        // Status breakdown — Programs
        $programsByStatus = [
            'proposal'  => ExtensionProgram::where('status', 'proposal')->count(),
            'ongoing'   => ExtensionProgram::where('status', 'ongoing')->count(),
            'completed' => ExtensionProgram::where('status', 'completed')->count(),
        ];

        // Status breakdown — Projects
        $projectsByStatus = [
            'proposal'  => ExtensionProject::where('status', 'proposal')->count(),
            'ongoing'   => ExtensionProject::where('status', 'ongoing')->count(),
            'completed' => ExtensionProject::where('status', 'completed')->count(),
        ];

        // Status breakdown — Activities
        $activitiesByStatus = [
            'proposal'  => ExtensionActivity::where('status', 'proposal')->count(),
            'ongoing'   => ExtensionActivity::where('status', 'ongoing')->count(),
            'completed' => ExtensionActivity::where('status', 'completed')->count(),
        ];

        // Overdue items
        $overdueActivities = ExtensionActivity::where('status', '!=', 'completed')
            ->whereNotNull('target_date')
            ->where('target_date', '<', $today)
            ->with('project')
            ->latest('target_date')
            ->take(10)
            ->get();

        $overdueProjects = ExtensionProject::where('status', '!=', 'completed')
            ->whereNotNull('target_end_date')
            ->where('target_end_date', '<', $today)
            ->latest('target_end_date')
            ->take(10)
            ->get();

        // Recent items
        $recentPrograms = ExtensionProgram::with('campus')
            ->latest()
            ->take(5)
            ->get();

        $recentProjects = ExtensionProject::with(['campus', 'program'])
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
