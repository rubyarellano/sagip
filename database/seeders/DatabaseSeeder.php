<?php

namespace Database\Seeders;

use App\Enums\IncidentCategory;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Enums\UserRole;
use App\Models\EmergencyContact;
use App\Models\Incident;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin Ruby Arellano
        $admin = User::create([
            'name' => 'Ruby Arellano',
            'first_name' => 'Ruby',
            'last_name' => 'Arellano',
            'email' => 'ruby.arellano@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '+63 917 123 4567',
            'role' => UserRole::Admin->value,
        ]);

        // 2. Seed Citizen Aly Rebancos
        $citizen1 = User::create([
            'name' => 'Aly Rebancos',
            'first_name' => 'Aly',
            'last_name' => 'Rebancos',
            'email' => 'alyrebancos@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '09123456789',
            'role' => UserRole::Citizen->value,
            'gender' => 'Female',
            'birthday' => '2004-06-09',
            'blood_type' => 'O-',
            'height' => '5\' 4"',
            'weight' => '120 lbs',
            'allergies' => 'Asthma, Penicillin sensitivity , seasonal pollen allergy, no acute heart condition',
            'device_id' => '#01024',
            'address' => 'Brgy. 10, Sorsogon City',
        ]);

        // Seed emergency contacts for Aly Rebancos
        EmergencyContact::create([
            'user_id' => $citizen1->id,
            'name' => 'Ruby Arellano',
            'relation' => 'Family',
            'phone' => '+63 917 123 4567',
        ]);

        // 3. Seed Citizen Aly Bautista
        $citizen2 = User::create([
            'name' => 'Aly Bautista',
            'first_name' => 'Aly',
            'last_name' => 'Bautista',
            'email' => 'alybautista@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '+63 917 123 4567',
            'role' => UserRole::Citizen->value,
            'gender' => 'Female',
            'birthday' => '1998-10-15',
            'address' => 'Brgy. Tulatula, Bicol',
        ]);

        // 4. Seed other citizen users from the Registered Users List
        $citizensData = [
            ['Maria Santos', 'Maria', 'Santos', 'maria@gmail.com', 'Brgy. San Jose, Bicol'],
            ['Jose Cruz', 'Jose', 'Cruz', 'jose@gmail.com', 'Brgy. Pandan, Bicol'],
            ['Ana Reyes', 'Ana', 'Reyes', 'ana@gmail.com', 'Brgy. Tastas, Bicol'],
            ['Ruby Orbase', 'Ruby', 'Orbase', 'ruby.or@gmail.com', 'Brgy. Pinamaniquian, Bicol'],
            ['Gela Velasco', 'Gela', 'Velasco', 'gela@gmail.com', 'Brgy. Bonga, Bicol'],
            ['Hazel Baguio', 'Hazel', 'Baguio', 'hazel@gmail.com', 'Brgy. Calzada, Bicol'],
            ['Iris Ricario', 'Iris', 'Ricario', 'iris@gmail.com', 'Brgy. Bagumbayan, Bicol'],
        ];

        foreach ($citizensData as $index => $data) {
            User::create([
                'name' => $data[0],
                'first_name' => $data[1],
                'last_name' => $data[2],
                'email' => $data[3],
                'password' => Hash::make('password'),
                'phone' => '+63 917 123 4567',
                'address' => $data[4],
                'role' => UserRole::Citizen->value,
                'device_id' => '#0200'.($index + 1),
            ]);
        }

        // 5. Seed Incidents to match the prototype listings
        // We force IDs using raw SQL or just create them sequentially.
        // Let's create incidents sequentially. To align the IDs exactly:
        // We will seed dummy incidents up to 15 first to simulate earlier incidents, then insert the ones shown in the UI.
        for ($i = 1; $i <= 15; $i++) {
            Incident::create([
                'user_id' => $citizen2->id,
                'category' => IncidentCategory::Medical->value,
                'severity' => IncidentSeverity::Low->value,
                'latitude' => 12.960 + ($i * 0.001),
                'longitude' => 124.000 - ($i * 0.001),
                'address' => 'Sample Street, Sorsogon',
                'status' => IncidentStatus::Resolved->value,
                'description' => 'Dummy incident #'.$i,
                'created_at' => Carbon::now()->subDays($i)->setTime(10, 0, 0),
            ]);
        }

        // Incident #16 (AH-016)
        Incident::create([
            'id' => 16,
            'user_id' => $citizen1->id,
            'category' => IncidentCategory::Flood->value, // Will show as Natural Disaster in some contexts
            'severity' => IncidentSeverity::Low->value,
            'latitude' => 12.9550,
            'longitude' => 124.0200,
            'address' => 'Goa, Bicol',
            'status' => IncidentStatus::Dispatched->value,
            'description' => 'Drainage backup and localized street flooding.',
            'created_at' => Carbon::now()->subDays(3)->setTime(15, 50, 0),
        ]);

        // Fillers from #17 to #26
        for ($i = 17; $i <= 26; $i++) {
            Incident::create([
                'user_id' => $citizen2->id,
                'category' => IncidentCategory::Other->value,
                'severity' => IncidentSeverity::Medium->value,
                'latitude' => 12.962 + ($i * 0.0005),
                'longitude' => 124.002 - ($i * 0.0005),
                'address' => 'Street '.$i.', Bicol',
                'status' => IncidentStatus::Dismissed->value,
                'description' => 'Dummy incident #'.$i,
                'created_at' => Carbon::now()->subDays(4)->setTime(10, 30, 0),
            ]);
        }

        // Incident #27 (AH-027)
        Incident::create([
            'id' => 27,
            'user_id' => $citizen1->id,
            'category' => IncidentCategory::Flood->value,
            'severity' => IncidentSeverity::High->value,
            'latitude' => 12.9715,
            'longitude' => 124.0150,
            'address' => 'San Jose St.',
            'status' => IncidentStatus::Dispatched->value,
            'description' => 'Severe flash flood near San Jose St.',
            'created_at' => Carbon::now()->subDays(2)->setTime(11, 0, 0),
        ]);

        // Incident #28 (AH-028)
        Incident::create([
            'id' => 28,
            'user_id' => $citizen2->id,
            'category' => IncidentCategory::Landslide->value,
            'severity' => IncidentSeverity::Low->value,
            'latitude' => 12.9580,
            'longitude' => 123.9960,
            'address' => 'Pandan Village Bicol',
            'status' => IncidentStatus::Dismissed->value,
            'description' => 'Minor earth movement. Road is clear.',
            'created_at' => Carbon::now()->subDays(1)->setTime(12, 10, 0),
        ]);

        // Incident #29 (AH-029)
        Incident::create([
            'id' => 29,
            'user_id' => $citizen2->id,
            'category' => IncidentCategory::Other->value,
            'severity' => IncidentSeverity::Medium->value,
            'latitude' => 12.9660,
            'longitude' => 124.0110,
            'address' => 'Goa Town Plaza',
            'status' => IncidentStatus::Dismissed->value,
            'description' => 'Report of structure debris blocking walkway.',
            'created_at' => Carbon::now()->subDays(1)->setTime(11, 50, 0),
        ]);

        // Incident #30 (AH-030)
        Incident::create([
            'id' => 30,
            'user_id' => $citizen2->id,
            'category' => IncidentCategory::Medical->value,
            'severity' => IncidentSeverity::Medium->value,
            'latitude' => 12.9690,
            'longitude' => 124.0040,
            'address' => 'Pandan Village Bicol',
            'status' => IncidentStatus::InProgress->value,
            'description' => 'Citizen reporting asthma attack, needs rescue dispatch.',
            'created_at' => Carbon::now()->setTime(11, 30, 0),
        ]);

        // Incident #31 (AH-031)
        Incident::create([
            'id' => 31,
            'user_id' => $citizen2->id,
            'category' => IncidentCategory::Fire->value,
            'severity' => IncidentSeverity::High->value,
            'latitude' => 12.9630,
            'longitude' => 124.0080,
            'address' => 'Zone 4, Goa Market Area',
            'status' => IncidentStatus::Resolved->value,
            'description' => 'Market stall fire resolved by BFP responders.',
            'created_at' => Carbon::now()->setTime(14, 20, 0),
        ]);

        $this->call(AppNotificationSeeder::class);
    }
}
