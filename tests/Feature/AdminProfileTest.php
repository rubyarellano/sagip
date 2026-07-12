<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('guests are redirected from admin profile page', function () {
    $response = $this->get(route('admin.profile'));
    $response->assertRedirect(route('login'));
});

test('citizen is redirected from admin profile page', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $this->actingAs($user);

    $response = $this->get(route('admin.profile'));
    $response->assertRedirect(route('dashboard'));
});

test('admin profile page can be rendered', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $this->actingAs($user);

    $response = $this->get(route('admin.profile'));
    $response->assertOk();
});

test('admin can update details and upload profile avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'role' => UserRole::Admin->value,
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);
    $this->actingAs($user);

    $file = UploadedFile::fake()->image('avatar.png');

    Livewire::test('pages::admin.profile')
        ->set('name', 'Updated Admin Name')
        ->set('email', 'updated@example.com')
        ->set('phone', '0987654321')
        ->set('avatarFile', $file)
        ->call('updateProfile')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Updated Admin Name');
    expect($user->email)->toBe('updated@example.com');
    expect($user->phone)->toBe('0987654321');
    expect($user->avatar)->not->toBeNull();

    Storage::disk('public')->assertExists($user->avatar);
});
