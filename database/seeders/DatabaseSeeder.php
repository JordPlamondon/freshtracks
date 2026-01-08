<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@freshtracks.test',
            'password' => Hash::make('password'),
        ]);

        $clients = [
            [
                'name' => 'Acme Corporation',
                'email' => 'contact@acme.com',
                'hourly_rate' => 150.00,
                'status' => 'active'
            ],
            [
                'name' => 'Tech Startup Inc',
                'email' => 'hello@techstartup.com',
                'hourly_rate' => 125.00,
                'status' => 'active'
            ],
            [
                'name' => 'Design Studio',
                'email' => 'info@designstudio.com',
                'hourly_rate' => 100.00,
                'status' => 'active'
            ]
        ];

        foreach ($clients as $clientData) {
            $client = Client::create($clientData);

            $projects = [
                [
                    'client_id' => $client->id,
                    'name' => 'Website Redesign',
                    'description' => 'Complete website redesign and development',
                    'status' => 'active'
                ],
                [
                    'client_id' => $client->id,
                    'name' => 'Mobile App',
                    'description' => 'iOS and Android mobile application',
                    'status' => $client->name === 'Acme Corporation' ? 'active' : 'completed'
                ]
            ];

            foreach ($projects as $projectData) {
                $project = Project::create($projectData);

                $timeEntries = [
                    [
                        'project_id' => $project->id,
                        'user_id' => $user->id,
                        'description' => 'Initial planning and setup',
                        'started_at' => Carbon::now()->subDays(5)->setTime(9, 0),
                        'stopped_at' => Carbon::now()->subDays(5)->setTime(12, 30),
                        'duration_minutes' => 210,
                        'is_billable' => true
                    ],
                    [
                        'project_id' => $project->id,
                        'user_id' => $user->id,
                        'description' => 'Development work',
                        'started_at' => Carbon::now()->subDays(3)->setTime(10, 0),
                        'stopped_at' => Carbon::now()->subDays(3)->setTime(16, 0),
                        'duration_minutes' => 360,
                        'is_billable' => true
                    ],
                    [
                        'project_id' => $project->id,
                        'user_id' => $user->id,
                        'description' => 'Code review and testing',
                        'started_at' => Carbon::now()->subDays(1)->setTime(14, 0),
                        'stopped_at' => Carbon::now()->subDays(1)->setTime(17, 30),
                        'duration_minutes' => 210,
                        'is_billable' => true
                    ]
                ];

                foreach ($timeEntries as $entryData) {
                    TimeEntry::create($entryData);
                }
            }
        }
    }
}
