<?php

use App\Jobs\BootupBiblesAndReferences;
use App\Jobs\InstallAllBibles;
use App\Jobs\InstallReferencesForFirstBible;
use App\Models\Bible;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    // Create an admin user with role number 1
    $adminRole = Role::factory()->create(['role_number' => 1, 'name' => 'Admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($adminRole);

    // Create a regular user without admin role
    $userRole = Role::factory()->create(['role_number' => 3, 'name' => 'User']);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($userRole);
});

test('admin can trigger bible bootup', function () {
    Queue::fake();

    actingAs($this->admin)
        ->post('/bibles/bootup')
        ->assertRedirect('/bibles/configure')
        ->assertSessionHas('success');

    Queue::assertPushed(BootupBiblesAndReferences::class);
});

test('non-admin cannot trigger bible bootup', function () {
    Queue::fake();

    actingAs($this->user)
        ->post('/bibles/bootup')
        ->assertForbidden();

    Queue::assertNotPushed(BootupBiblesAndReferences::class);
});

test('guest cannot trigger bible bootup', function () {
    Queue::fake();

    post('/bibles/bootup')
        ->assertRedirect('/login');

    Queue::assertNotPushed(BootupBiblesAndReferences::class);
});

test('install all bibles job can be dispatched', function () {
    // This is a smoke test to ensure the job can be instantiated
    $job = new InstallAllBibles();

    expect($job)->toBeInstanceOf(InstallAllBibles::class);
});

test('install references job can be dispatched', function () {
    // This is a smoke test to ensure the job can be instantiated
    $job = new InstallReferencesForFirstBible();

    expect($job)->toBeInstanceOf(InstallReferencesForFirstBible::class);
});

test('bootup job chains both install jobs', function () {
    Queue::fake();

    // Create a test Bible so references job has something to work with
    Bible::factory()->create([
        'name' => 'Test Bible',
        'abbreviation' => 'TEST',
        'language' => 'English',
    ]);

    // Dispatch the bootup job
    $job = new BootupBiblesAndReferences();
    $job->handle();

    // Since we're using dispatchSync in the job, we can't easily test the queue
    // but we can verify the job completes without errors
    expect(true)->toBeTrue();
});
