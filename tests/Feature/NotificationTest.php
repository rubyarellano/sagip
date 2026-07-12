<?php

use App\Enums\UserRole;
use App\Models\AppNotification;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from notification page', function () {
    $response = $this->get(route('notifications'));
    $response->assertRedirect(route('login'));
});

test('guests are redirected to login from admin notification page', function () {
    $response = $this->get(route('admin.notifications'));
    $response->assertRedirect(route('login'));
});

test('authenticated citizen can render citizen notification page', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $this->actingAs($user);

    $response = $this->get(route('notifications'));
    $response->assertOk();
});

test('authenticated admin can render admin notification page', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $this->actingAs($user);

    $response = $this->get(route('admin.notifications'));
    $response->assertOk();
});

test('admin is redirected to admin notifications when visiting citizen notifications', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $this->actingAs($user);

    $response = $this->get(route('notifications'));
    $response->assertRedirect(route('admin.notifications'));
});

test('citizen is redirected to dashboard when visiting admin notifications', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $this->actingAs($user);

    $response = $this->get(route('admin.notifications'));
    $response->assertRedirect(route('dashboard'));
});

test('citizens only see their own notifications', function () {
    $user1 = User::factory()->create(['role' => UserRole::Citizen->value]);
    $user2 = User::factory()->create(['role' => UserRole::Citizen->value]);

    $notif1 = AppNotification::factory()->create([
        'user_id' => $user1->id,
        'title' => 'User 1 Notification',
    ]);

    $notif2 = AppNotification::factory()->create([
        'user_id' => $user2->id,
        'title' => 'User 2 Notification',
    ]);

    $this->actingAs($user1);

    Livewire::test('pages::notifications')
        ->assertSee('User 1 Notification')
        ->assertDontSee('User 2 Notification');
});

test('citizens can mark a notification as read', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $notification = AppNotification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::notifications')
        ->call('markAsRead', $notification->id)
        ->assertHasNoErrors();

    expect($notification->refresh()->is_read)->toBeTrue();
});

test('citizens can delete a notification', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $notification = AppNotification::factory()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::notifications')
        ->call('deleteNotification', $notification->id)
        ->assertHasNoErrors();

    expect(AppNotification::find($notification->id))->toBeNull();
});

test('citizens can mark all notifications as read', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    $notifications = AppNotification::factory()->count(3)->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::notifications')
        ->call('markAllAsRead')
        ->assertHasNoErrors();

    foreach ($notifications as $notification) {
        expect($notification->refresh()->is_read)->toBeTrue();
    }
});

test('citizens can clear all notifications', function () {
    $user = User::factory()->create(['role' => UserRole::Citizen->value]);
    AppNotification::factory()->count(3)->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::notifications')
        ->call('clearAll')
        ->assertHasNoErrors();

    expect($user->appNotifications()->count())->toBe(0);
});
