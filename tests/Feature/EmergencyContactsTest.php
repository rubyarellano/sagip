<?php

use App\Enums\UserRole;
use App\Models\EmergencyContact;
use App\Models\User;
use Livewire\Livewire;

test('emergency contacts page can be rendered for citizen', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $this->actingAs($user);

    $response = $this->get(route('emergency-contacts'));
    $response->assertOk();
    $response->assertSee('Core Responders');
});

test('admin is redirected from emergency contacts page to admin dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $this->actingAs($user);

    $response = $this->get(route('emergency-contacts'));
    $response->assertRedirect(route('admin.dashboard'));
});

test('citizen can add multiple emergency contacts on emergency contacts page', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $this->actingAs($user);

    Livewire::test('pages::emergency-contacts')
        ->set('name', 'Authority Contact One')
        ->set('relation', 'Father')
        ->set('phone', '09123456789')
        ->call('saveContact')
        ->assertHasNoErrors()
        ->set('name', 'Authority Contact Two')
        ->set('relation', 'Mother')
        ->set('phone', '09987654321')
        ->call('saveContact')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('emergency_contacts', [
        'user_id' => $user->id,
        'name' => 'Authority Contact One',
        'relation' => 'Father',
        'phone' => '09123456789',
    ]);

    $this->assertDatabaseHas('emergency_contacts', [
        'user_id' => $user->id,
        'name' => 'Authority Contact Two',
        'relation' => 'Mother',
        'phone' => '09987654321',
    ]);
});

test('citizen can delete emergency contacts on emergency contacts page', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $this->actingAs($user);

    $contact = EmergencyContact::create([
        'user_id' => $user->id,
        'name' => 'Delete Contact Test',
        'relation' => 'Uncle',
        'phone' => '09123456789',
    ]);

    Livewire::test('pages::emergency-contacts')
        ->call('deleteContact', $contact->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('emergency_contacts', [
        'id' => $contact->id,
    ]);
});
