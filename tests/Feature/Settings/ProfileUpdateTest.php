<?php

use App\Models\EmergencyContact;
use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('user can add multiple emergency contacts on profile page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('pages::settings.profile')
        ->set('new_contact_name', 'Contact One')
        ->set('new_contact_relation', 'Brother')
        ->set('new_contact_phone', '09123456789')
        ->call('addEmergencyContact')
        ->assertHasNoErrors()
        ->set('new_contact_name', 'Contact Two')
        ->set('new_contact_relation', 'Sister')
        ->set('new_contact_phone', '09987654321')
        ->call('addEmergencyContact')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('emergency_contacts', [
        'user_id' => $user->id,
        'name' => 'Contact One',
        'relation' => 'Brother',
        'phone' => '09123456789',
    ]);

    $this->assertDatabaseHas('emergency_contacts', [
        'user_id' => $user->id,
        'name' => 'Contact Two',
        'relation' => 'Sister',
        'phone' => '09987654321',
    ]);

    expect($user->emergencyContacts()->count())->toBe(2);
});

test('user can delete emergency contacts on profile page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $contact = EmergencyContact::create([
        'user_id' => $user->id,
        'name' => 'Delete Me',
        'relation' => 'Friend',
        'phone' => '09112233445',
    ]);

    Livewire::test('pages::settings.profile')
        ->call('deleteEmergencyContact', $contact->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('emergency_contacts', [
        'id' => $contact->id,
    ]);
});
