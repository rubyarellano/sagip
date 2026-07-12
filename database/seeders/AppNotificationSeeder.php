<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'ruby.are.ruby@gmail.com')->orWhere('email', 'ruby.arellano@sagip.ph')->first();
        $citizen = User::where('email', 'alyrebancos@gmail.com')->first();

        if ($citizen) {
            AppNotification::create([
                'user_id' => $citizen->id,
                'title' => 'ResQband Connected',
                'body' => 'Your ResQband Smart Watch (#01024) has been successfully paired with your profile.',
                'type' => 'success',
                'is_read' => true,
                'created_at' => Carbon::now()->subHours(8),
            ]);

            AppNotification::create([
                'user_id' => $citizen->id,
                'title' => 'Emergency Contact Added',
                'body' => 'Ruby Arellano has been added as your primary emergency contact.',
                'type' => 'info',
                'is_read' => true,
                'created_at' => Carbon::now()->subHours(6),
            ]);

            AppNotification::create([
                'user_id' => $citizen->id,
                'title' => 'Incident Report Submitted',
                'body' => 'Your Flood incident report in Brgy. 10, Sorsogon City has been received.',
                'type' => 'warning',
                'is_read' => false,
                'link' => route('dashboard'),
                'created_at' => Carbon::now()->subHours(2),
            ]);

            AppNotification::create([
                'user_id' => $citizen->id,
                'title' => 'Responder Dispatched',
                'body' => 'Bicol CDRRMO has received your distress report for Flood and is dispatching a rescue team.',
                'type' => 'danger',
                'is_read' => false,
                'link' => route('dashboard'),
                'created_at' => Carbon::now()->subMinutes(15),
            ]);
        }

        if ($admin) {
            AppNotification::create([
                'user_id' => $admin->id,
                'title' => 'Weekly System Backup',
                'body' => 'Weekly automated system backup completed successfully.',
                'type' => 'success',
                'is_read' => true,
                'created_at' => Carbon::now()->subDays(2),
            ]);

            AppNotification::create([
                'user_id' => $admin->id,
                'title' => 'New Device Registered',
                'body' => 'User Iris Ricario paired a new ResQband (#02007).',
                'type' => 'info',
                'is_read' => true,
                'created_at' => Carbon::now()->subHours(12),
            ]);

            AppNotification::create([
                'user_id' => $admin->id,
                'title' => 'Active Distress Alert',
                'body' => 'Jose Cruz reported: \'Citizen reporting asthma attack, needs rescue dispatch.\' (Medium Severity).',
                'type' => 'warning',
                'is_read' => false,
                'link' => route('admin.live-map'),
                'created_at' => Carbon::now()->subHours(1),
            ]);

            AppNotification::create([
                'user_id' => $admin->id,
                'title' => 'Critical Incident Reported',
                'body' => 'Aly Rebancos reported: \'Severe flash flood near San Jose St.\' (High Severity).',
                'type' => 'danger',
                'is_read' => false,
                'link' => route('admin.alert-history'),
                'created_at' => Carbon::now()->subMinutes(5),
            ]);
        }
    }
}
