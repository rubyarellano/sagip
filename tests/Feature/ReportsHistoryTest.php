<?php

use App\Enums\IncidentCategory;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Enums\UserRole;
use App\Models\Incident;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page from reports history', function () {
    $response = $this->get(route('reports-history'));
    $response->assertRedirect(route('login'));
});

test('admin is redirected from reports history to admin dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $this->actingAs($user);

    $response = $this->get(route('reports-history'));
    $response->assertRedirect(route('admin.dashboard'));
});

test('citizens can visit the reports history page and see their reports', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $otherUser = User::factory()->create(['role' => UserRole::Citizen->value]);
    $this->actingAs($user);

    $userIncident = Incident::create([
        'user_id' => $user->id,
        'category' => IncidentCategory::Flood->value,
        'severity' => IncidentSeverity::High->value,
        'latitude' => 12.9715,
        'longitude' => 124.0150,
        'address' => 'San Jose St.',
        'status' => IncidentStatus::Dispatched->value,
        'description' => 'Severe flash flood near San Jose St.',
    ]);

    $otherIncident = Incident::create([
        'user_id' => $otherUser->id,
        'category' => IncidentCategory::Fire->value,
        'severity' => IncidentSeverity::High->value,
        'latitude' => 12.9630,
        'longitude' => 124.0080,
        'address' => 'Zone 4, Goa Market Area',
        'status' => IncidentStatus::Resolved->value,
        'description' => 'Market stall fire resolved by BFP responders.',
    ]);

    $response = $this->get(route('reports-history'));
    $response->assertOk();
    $response->assertSee('My Reports History');
    $response->assertSee('San Jose St.');
    $response->assertDontSee('Zone 4, Goa Market Area');
});

test('citizens can filter and search their reports history', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $this->actingAs($user);

    $floodIncident = Incident::create([
        'user_id' => $user->id,
        'category' => IncidentCategory::Flood->value,
        'severity' => IncidentSeverity::High->value,
        'latitude' => 12.9715,
        'longitude' => 124.0150,
        'address' => 'San Jose St.',
        'status' => IncidentStatus::Dispatched->value,
        'description' => 'Severe flash flood near San Jose St.',
    ]);

    $fireIncident = Incident::create([
        'user_id' => $user->id,
        'category' => IncidentCategory::Fire->value,
        'severity' => IncidentSeverity::Medium->value,
        'latitude' => 12.9630,
        'longitude' => 124.0080,
        'address' => 'Zone 4, Goa Market Area',
        'status' => IncidentStatus::Resolved->value,
        'description' => 'Market stall fire.',
    ]);

    Livewire::test('pages::reports-history')
        ->assertSee('San Jose St.')
        ->assertSee('Zone 4, Goa Market Area')
        // Filter by category
        ->set('category', 'Flood')
        ->assertSee('San Jose St.')
        ->assertDontSee('Zone 4, Goa Market Area')
        // Reset category and filter by status
        ->set('category', '')
        ->set('status', 'Resolved')
        ->assertDontSee('San Jose St.')
        ->assertSee('Zone 4, Goa Market Area')
        // Reset status and search
        ->set('status', '')
        ->set('search', 'flash flood')
        ->assertSee('San Jose St.')
        ->assertDontSee('Zone 4, Goa Market Area');
});

test('submitting a new incident redirects the citizen to reports history', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $this->actingAs($user);

    Livewire::test('pages::report-incident')
        ->set('category', IncidentCategory::Flood->value)
        ->set('severity', IncidentSeverity::Medium->value)
        ->set('address', 'Brgy. 10, Sorsogon City')
        ->set('status', 'In Danger')
        ->set('description', 'The flood level is rising rapidly.')
        ->set('reporterName', 'John Doe')
        ->set('reporterPhone', '09123456789')
        ->call('submitReport')
        ->assertHasNoErrors()
        ->assertRedirect(route('reports-history'));

    $this->assertDatabaseHas('incidents', [
        'user_id' => $user->id,
        'category' => IncidentCategory::Flood->value,
        'status' => IncidentStatus::Dispatched->value,
        'description' => 'The flood level is rising rapidly.',
        'reporter_name' => 'John Doe',
        'reporter_phone' => '09123456789',
    ]);
});
