<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\ExtensionActivity;
use App\Models\ExtensionBeneficiary;
use App\Models\ExtensionBudgetItem;
use App\Models\ExtensionProgram;
use App\Models\ExtensionProgramMember;
use App\Models\ExtensionProject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |------------------------------------------------------------------
        | 1. Campuses (CHMSU)
        |------------------------------------------------------------------
        */
        $campuses = [
            ['name' => 'CHMSU - Talisay Campus',        'address' => 'Talisay, Negros Occidental'],
            ['name' => 'CHMSU - Alijis Campus',          'address' => 'Brgy. Alijis, Bacolod City, Negros Occidental'],
            ['name' => 'CHMSU - Fortune Towne Campus',   'address' => 'Fortune Towne, Bacolod City, Negros Occidental'],
            ['name' => 'CHMSU - Binalbagan Campus',      'address' => 'Binalbagan, Negros Occidental'],
        ];

        foreach ($campuses as $c) {
            Campus::create($c);
        }

        $talisay = Campus::where('name', 'like', '%Talisay%')->first();
        $alijis  = Campus::where('name', 'like', '%Alijis%')->first();

        /*
        |------------------------------------------------------------------
        | 2. Users
        |------------------------------------------------------------------
        */
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name'  => 'User',
            'email'     => 'admin@chmsu.edu.ph',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
            'campus_id' => null,
        ]);

        $staff = User::create([
            'first_name' => 'Extension',
            'last_name'  => 'Staff',
            'email'     => 'staff@chmsu.edu.ph',
            'password'  => Hash::make('password'),
            'role'      => 'extension_staff',
            'is_active' => true,
            'campus_id' => null,
        ]);

        /*
        |------------------------------------------------------------------
        | 3. Sample Extension Program (based on CEFP form)
        |------------------------------------------------------------------
        */
        $program = ExtensionProgram::create([
            'ic_no'                      => 'IC-2025-001',
            'title'                      => 'Community Empowerment Through Sustainable Livelihood Development',
            'proponent_name'             => 'Dr. Maria Santos',
            'division_unit'              => 'College of Arts and Sciences',
            'proponent_address'          => 'CHMSU, Talisay, Negros Occidental',
            'contact_no'                 => '09171234567',
            'cooperating_entities'       => 'Local Government Unit of Talisay, Department of Agriculture',
            'cooperating_entity_address' => 'Municipal Hall, Talisay, Negros Occidental',
            'program_location'           => 'Talisay, Negros Occidental',
            'beneficiary_class'          => 'Farmers, Fisherfolk, Out-of-school Youth',
            'target_recipients'          => 150,
            'funding_chmsu_gaa'          => 50000.00,
            'funding_chmsu_gaa_note'     => 'GAA FY 2025',
            'funding_chmsu_stf'          => 25000.00,
            'funding_collaborator'       => 30000.00,
            'funding_collaborator_note'  => 'LGU Counterpart',
            'funding_total'              => 105000.00,
            'target_start_date'          => '2025-06-01',
            'target_end_date'            => '2026-05-31',
            'program_leader'             => 'Dr. Maria Santos',
            'rationale'                  => 'The municipality of Talisay has a large population of farmers and fisherfolk who need support in sustainable livelihood practices. This program aims to provide training, resources, and continuing education to empower these communities toward self-sufficiency.',
            'conceptual_framework'       => 'Input-Process-Output model aligned with CHED extension guidelines and UN Sustainable Development Goals.',
            'general_objective'          => 'To empower marginalized communities in Talisay through sustainable livelihood development programs.',
            'specific_objectives'        => "1. Conduct skills training on organic farming for 100 farmers.\n2. Provide financial literacy seminars for 50 out-of-school youth.\n3. Establish a community garden as a demonstration farm.",
            'methodology'                => "Phase 1: Community Needs Assessment\nPhase 2: Capacity Building and Training\nPhase 3: Implementation and Monitoring\nPhase 4: Evaluation and Sustainability Planning",
            'status'                     => 'ongoing',
            'campus_id'                  => $talisay->id,
            'created_by'                 => $admin->id,
        ]);

        // Program members
        foreach ([
            ['name' => 'Dr. Maria Santos',    'responsibility' => 'Program Leader / Overall Coordinator'],
            ['name' => 'Prof. Juan dela Cruz', 'responsibility' => 'Training Facilitator'],
            ['name' => 'Ms. Ana Reyes',        'responsibility' => 'Community Liaison Officer'],
            ['name' => 'Mr. Pedro Garcia',     'responsibility' => 'Finance & Documentation'],
        ] as $m) {
            ExtensionProgramMember::create(array_merge($m, [
                'extension_program_id' => $program->id,
            ]));
        }

        /*
        |------------------------------------------------------------------
        | 4. Projects Under the Program
        |------------------------------------------------------------------
        */
        $project1 = ExtensionProject::create([
            'extension_program_id' => $program->id,
            'title'                => 'Organic Farming Skills Training',
            'description'          => 'A hands-on training program teaching organic farming techniques to local farmers, including composting, natural pest control, and crop rotation.',
            'persons_responsible'   => 'Prof. Juan dela Cruz, Ms. Ana Reyes',
            'budget_requirement'   => 45000.00,
            'budget_source'        => 'CHMSU GAA + LGU Counterpart',
            'indicators_output'    => '100 farmers trained, 1 demonstration farm established',
            'target_start_date'    => '2025-06-15',
            'target_end_date'      => '2025-12-15',
            'status'               => 'ongoing',
            'campus_id'            => $talisay->id,
            'created_by'           => $admin->id,
        ]);

        $project2 = ExtensionProject::create([
            'extension_program_id' => $program->id,
            'title'                => 'Financial Literacy for Out-of-School Youth',
            'description'          => 'Seminar series on basic financial management, savings, budgeting, and entrepreneurship for out-of-school youth in Talisay.',
            'persons_responsible'   => 'Mr. Pedro Garcia',
            'budget_requirement'   => 20000.00,
            'budget_source'        => 'CHMSU STF',
            'indicators_output'    => '50 youth participants, post-seminar assessment scores',
            'target_start_date'    => '2025-08-01',
            'target_end_date'      => '2025-11-30',
            'status'               => 'ongoing',
            'campus_id'            => $talisay->id,
            'created_by'           => $staff->id,
        ]);

        /*
        |------------------------------------------------------------------
        | 5. Standalone Project (no parent program)
        |------------------------------------------------------------------
        */
        $standalone = ExtensionProject::create([
            'extension_program_id' => null,
            'title'                => 'Coastal Cleanup and Environmental Awareness Drive',
            'description'          => 'A one-time community service project involving coastal cleanup and environmental awareness campaign in partnership with DENR.',
            'persons_responsible'   => 'Dr. Lena Cruz, Student Volunteers',
            'budget_requirement'   => 15000.00,
            'budget_source'        => 'CHMSU GAA',
            'indicators_output'    => '200 kg waste collected, 80 community participants',
            'target_start_date'    => '2025-09-20',
            'target_end_date'      => '2025-09-20',
            'status'               => 'proposal',
            'campus_id'            => $alijis->id,
            'created_by'           => $staff->id,
        ]);

        /*
        |------------------------------------------------------------------
        | 6. Activities
        |------------------------------------------------------------------
        */
        foreach ([
            [
                'extension_project_id' => $project1->id,
                'title'                => 'Pre-Training Assessment',
                'description'          => 'Baseline survey and assessment of farming knowledge among participants.',
                'persons_responsible'   => 'Ms. Ana Reyes',
                'budget_requirement'   => 5000.00,
                'indicators_output'    => 'Survey forms completed for 100 farmers',
                'target_date'          => '2025-06-20',
                'completion_date'      => '2025-06-22',
                'status'               => 'completed',
                'created_by'           => $admin->id,
            ],
            [
                'extension_project_id' => $project1->id,
                'title'                => 'Composting Workshop (Batch 1)',
                'description'          => 'Hands-on composting training for the first batch of 50 farmers.',
                'persons_responsible'   => 'Prof. Juan dela Cruz',
                'budget_requirement'   => 12000.00,
                'indicators_output'    => '50 farmers trained in composting',
                'target_date'          => '2025-07-15',
                'completion_date'      => null,
                'status'               => 'ongoing',
                'created_by'           => $admin->id,
            ],
            [
                'extension_project_id' => $project1->id,
                'title'                => 'Natural Pest Control Seminar',
                'description'          => 'Lecture and demonstration on organic pest control methods.',
                'persons_responsible'   => 'Prof. Juan dela Cruz',
                'budget_requirement'   => 8000.00,
                'indicators_output'    => '100 farmers aware of natural pest control',
                'target_date'          => '2025-09-01',
                'completion_date'      => null,
                'status'               => 'ongoing',
                'created_by'           => $staff->id,
            ],
            [
                'extension_project_id' => $standalone->id,
                'title'                => 'Coastal Cleanup Activity',
                'description'          => 'Beach cleanup drive with student volunteers and community members.',
                'persons_responsible'   => 'Dr. Lena Cruz',
                'budget_requirement'   => 10000.00,
                'indicators_output'    => '200 kg waste collected',
                'target_date'          => '2025-09-20',
                'completion_date'      => null,
                'status'               => 'proposal',
                'created_by'           => $staff->id,
            ],
        ] as $a) {
            ExtensionActivity::create($a);
        }

        /*
        |------------------------------------------------------------------
        | 7. Beneficiaries
        |------------------------------------------------------------------
        */
        foreach ([
            ['extension_project_id' => $project1->id, 'name' => 'Juan Magbanua',               'address' => 'Brgy. Concepcion, Talisay',      'contact_no' => '09181234567', 'organization' => 'Talisay Farmers Association'],
            ['extension_project_id' => $project1->id, 'name' => 'Rosa Villanueva',             'address' => 'Brgy. Dos Hermanas, Talisay',    'contact_no' => '09192345678', 'organization' => 'Talisay Farmers Association'],
            ['extension_project_id' => $project1->id, 'name' => 'Pedro Ramos',                 'address' => 'Brgy. Matab-ang, Talisay',       'contact_no' => '09203456789', 'organization' => null],
            ['extension_project_id' => $project2->id, 'name' => 'Mark Anthony Tan',            'address' => 'Brgy. Zone 12, Talisay',         'contact_no' => '09214567890', 'organization' => null],
            ['extension_project_id' => $project2->id, 'name' => 'Jena Mae Lopez',              'address' => 'Brgy. Matab-ang, Talisay',       'contact_no' => '09225678901', 'organization' => 'Talisay Youth Council'],
            ['extension_project_id' => $standalone->id, 'name' => 'Brgy. Captain Leo Gonzales', 'address' => 'Brgy. Alijis, Bacolod City',     'contact_no' => '09236789012', 'organization' => 'Barangay Alijis'],
        ] as $b) {
            ExtensionBeneficiary::create($b);
        }

        /*
        |------------------------------------------------------------------
        | 8. Budget Items
        |------------------------------------------------------------------
        */
        foreach ([
            ['extension_project_id' => $project1->id,    'location' => 'Talisay Demo Farm',           'item_description' => 'Seeds and fertilizers',        'total_budget' => 15000.00],
            ['extension_project_id' => $project1->id,    'location' => 'CHMSU Talisay Campus',        'item_description' => 'Training materials & handouts', 'total_budget' => 8000.00],
            ['extension_project_id' => $project1->id,    'location' => 'Talisay Demo Farm',           'item_description' => 'Composting equipment',          'total_budget' => 12000.00],
            ['extension_project_id' => $project2->id,    'location' => 'CHMSU Talisay Function Hall', 'item_description' => 'Seminar venue & snacks',        'total_budget' => 10000.00],
            ['extension_project_id' => $standalone->id,  'location' => 'Alijis Coastal Area',         'item_description' => 'Gloves, bags, cleaning tools',  'total_budget' => 8000.00],
        ] as $bi) {
            ExtensionBudgetItem::create($bi);
        }

        $this->command->info('✅ URESIMS Extension Services Module seeded successfully!');
        $this->command->info('   Admin login:  admin@chmsu.edu.ph / password');
        $this->command->info('   Staff login:  staff@chmsu.edu.ph / password');
    }
}
