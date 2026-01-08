<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoResetSeeder extends Seeder
{
    /**
     * Reset the demo database to a clean state.
     * Run via: php artisan db:seed --class=DemoResetSeeder
     * Or via: php artisan demo:reset
     */
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');
        InvoiceItem::truncate();
        Invoice::truncate();
        TimeEntry::truncate();
        Project::truncate();
        Client::truncate();
        User::truncate();
        DB::statement('PRAGMA foreign_keys = ON');

        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@freshtracks.test',
            'password' => Hash::make('password'),
        ]);

        $acme = Client::create([
            'name' => 'Acme Corporation',
            'email' => 'contact@acme.com',
            'hourly_rate' => 150.00,
            'status' => 'active'
        ]);

        $techStartup = Client::create([
            'name' => 'Tech Startup Inc',
            'email' => 'hello@techstartup.io',
            'hourly_rate' => 125.00,
            'status' => 'active'
        ]);

        $designStudio = Client::create([
            'name' => 'Creative Design Co',
            'email' => 'projects@creativedesign.co',
            'hourly_rate' => 100.00,
            'status' => 'active'
        ]);

        $oldClient = Client::create([
            'name' => 'Legacy Systems Ltd',
            'email' => 'admin@legacysystems.com',
            'hourly_rate' => 175.00,
            'status' => 'inactive'
        ]);

        $acmeWebsite = Project::create([
            'client_id' => $acme->id,
            'name' => 'Website Redesign',
            'description' => 'Complete website redesign with new branding',
            'status' => 'active'
        ]);

        $acmeApi = Project::create([
            'client_id' => $acme->id,
            'name' => 'API Integration',
            'description' => 'Third-party API integration and documentation',
            'status' => 'active'
        ]);

        $startupMvp = Project::create([
            'client_id' => $techStartup->id,
            'name' => 'MVP Development',
            'description' => 'Minimum viable product for Series A pitch',
            'status' => 'active'
        ]);

        $startupMobile = Project::create([
            'client_id' => $techStartup->id,
            'name' => 'Mobile App',
            'description' => 'iOS and Android companion app',
            'status' => 'active'
        ]);

        $designBranding = Project::create([
            'client_id' => $designStudio->id,
            'name' => 'Brand Identity',
            'description' => 'Logo, colors, and brand guidelines',
            'status' => 'active'
        ]);

        $legacyMaintenance = Project::create([
            'client_id' => $oldClient->id,
            'name' => 'System Maintenance',
            'description' => 'Monthly maintenance and support',
            'status' => 'inactive'
        ]);

        $createEntry = function ($project, $user, $description, $daysAgo, $startHour, $durationMinutes, $billable = true) {
            $startedAt = Carbon::now()->subDays($daysAgo)->setTime($startHour, 0, 0);
            $stoppedAt = $startedAt->copy()->addMinutes($durationMinutes);

            return TimeEntry::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'description' => $description,
                'started_at' => $startedAt,
                'stopped_at' => $stoppedAt,
                'duration_minutes' => $durationMinutes,
                'is_billable' => $billable,
            ]);
        };

        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;

        if ($dayOfWeek >= 1) {
            $daysAgo = $dayOfWeek - 1;
            if ($dayOfWeek == 0) $daysAgo = 6; // Sunday
            $createEntry($acmeWebsite, $user, 'Homepage wireframes and mockups', $daysAgo, 9, 180);
            $createEntry($startupMvp, $user, 'Database schema design', $daysAgo, 14, 120);
        }

        if ($dayOfWeek >= 2 || $dayOfWeek == 0) {
            $daysAgo = $dayOfWeek == 0 ? 5 : $dayOfWeek - 2;
            $createEntry($acmeWebsite, $user, 'Frontend development - header and navigation', $daysAgo, 10, 240);
            $createEntry($acmeApi, $user, 'API endpoint documentation', $daysAgo, 15, 90);
        }

        if ($dayOfWeek >= 3 || $dayOfWeek == 0) {
            $daysAgo = $dayOfWeek == 0 ? 4 : $dayOfWeek - 3;
            $createEntry($startupMvp, $user, 'User authentication implementation', $daysAgo, 9, 300);
            $createEntry($designBranding, $user, 'Logo concepts - round 1', $daysAgo, 16, 120);
        }

        if ($dayOfWeek >= 4 || $dayOfWeek == 0) {
            $daysAgo = $dayOfWeek == 0 ? 3 : $dayOfWeek - 4;
            $createEntry($acmeWebsite, $user, 'Responsive design implementation', $daysAgo, 10, 210);
            $createEntry($startupMobile, $user, 'React Native project setup', $daysAgo, 14, 150);
        }

        if ($dayOfWeek >= 5 || $dayOfWeek == 0) {
            $daysAgo = $dayOfWeek == 0 ? 2 : $dayOfWeek - 5;
            $createEntry($acmeApi, $user, 'Payment gateway integration', $daysAgo, 9, 270);
            $createEntry($designBranding, $user, 'Client feedback revisions', $daysAgo, 15, 90);
        }

        if ($dayOfWeek != 1) {
            $createEntry($startupMvp, $user, 'Bug fixes and code review', 1, 10, 180);
        }

        $createEntry($acmeWebsite, $user, 'Contact form and footer', 0, 9, 120);

        $createEntry($acmeWebsite, $user, 'Project kickoff meeting', 7, 10, 60);
        $createEntry($acmeWebsite, $user, 'Requirements gathering', 7, 14, 120);
        $createEntry($startupMvp, $user, 'Technical architecture planning', 8, 9, 180);
        $createEntry($designBranding, $user, 'Brand discovery workshop', 9, 13, 150);
        $createEntry($acmeApi, $user, 'API security audit', 10, 10, 240);

        $paidInvoice = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $oldClient->id,
            'issue_date' => Carbon::now()->subDays(30),
            'due_date' => Carbon::now()->subDays(15),
            'total_amount' => 2625.00,
            'status' => 'paid',
        ]);

        InvoiceItem::create([
            'invoice_id' => $paidInvoice->id,
            'description' => 'System Maintenance - October',
            'hours' => 15,
            'rate' => 175.00,
            'amount' => 2625.00,
        ]);

        $draftInvoice = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $designStudio->id,
            'issue_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(30),
            'total_amount' => 450.00,
            'status' => 'draft',
        ]);

        InvoiceItem::create([
            'invoice_id' => $draftInvoice->id,
            'description' => 'Brand Identity - Logo concepts',
            'hours' => 4.5,
            'rate' => 100.00,
            'amount' => 450.00,
        ]);

        $this->command->info('Demo data has been reset successfully!');
    }
}
