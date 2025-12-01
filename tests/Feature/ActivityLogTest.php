<?php

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;

test('activity log is created when user deletes account', function () {
    $user = User::factory()->create();
    $userName = $user->name;

    $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $log = ActivityLog::where('action', 'account_deletion')
        ->where('description', 'like', "%{$userName}%")
        ->first();

    expect($log)->not->toBeNull();
    expect($log->description)->toContain($userName);
});

test('activity log is created when admin updates user roles', function () {
    $admin = User::factory()->create();
    $role = Role::factory()->create(['name' => 'admin', 'role_number' => 1]);
    $admin->roles()->attach($role);

    $targetUser = User::factory()->create();
    $newRole = Role::factory()->create(['name' => 'editor', 'role_number' => 2]);

    $this
        ->actingAs($admin)
        ->put(route('update_roles', $targetUser), [
            'role_ids' => [$newRole->id],
        ]);

    $log = ActivityLog::where('action', 'role_update')
        ->where('subject_user_id', $targetUser->id)
        ->first();

    expect($log)->not->toBeNull();
    expect($log->user_id)->toBe($admin->id);
    expect($log->description)->toContain($targetUser->name);
});

test('activity log is created when admin deletes user', function () {
    $admin = User::factory()->create();
    $role = Role::factory()->create(['name' => 'admin', 'role_number' => 1]);
    $admin->roles()->attach($role);

    $targetUser = User::factory()->create();
    $targetUserName = $targetUser->name;

    $this
        ->actingAs($admin)
        ->delete(route('delete_user', $targetUser));

    $log = ActivityLog::where('action', 'user_deletion_by_admin')
        ->where('description', 'like', "%{$targetUserName}%")
        ->first();

    expect($log)->not->toBeNull();
    expect($log->user_id)->toBe($admin->id);
});

test('activity log records ip address', function () {
    $user = User::factory()->create();

    ActivityLog::log('test_action', 'Test description', $user->id);

    $log = ActivityLog::where('action', 'test_action')->first();

    expect($log)->not->toBeNull();
    expect($log->ip_address)->not->toBeNull();
});

test('activity log can be filtered by action', function () {
    $admin = User::factory()->create();
    $role = Role::factory()->create(['name' => 'admin', 'role_number' => 1]);
    $admin->roles()->attach($role);

    ActivityLog::create([
        'user_id' => $admin->id,
        'action' => 'account_deletion',
        'description' => 'Test 1',
        'ip_address' => '127.0.0.1',
    ]);

    ActivityLog::create([
        'user_id' => $admin->id,
        'action' => 'role_update',
        'description' => 'Test 2',
        'ip_address' => '127.0.0.1',
    ]);

    $logs = ActivityLog::where('action', 'account_deletion')->get();

    expect($logs)->toHaveCount(1);
    expect($logs->first()->action)->toBe('account_deletion');
});

test('activity log model has relationships', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    ActivityLog::create([
        'user_id' => $user->id,
        'subject_user_id' => $targetUser->id,
        'action' => 'test_action',
        'description' => 'Test description',
        'ip_address' => '127.0.0.1',
    ]);

    $log = ActivityLog::where('action', 'test_action')->first();

    expect($log->user)->not->toBeNull();
    expect($log->user->id)->toBe($user->id);
    expect($log->subjectUser)->not->toBeNull();
    expect($log->subjectUser->id)->toBe($targetUser->id);
});
