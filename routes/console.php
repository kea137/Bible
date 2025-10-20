<?php

use App\Jobs\BootupBiblesAndReferences;
use App\Models\Bible;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('seed:admin', function () {
    $this->call('migrate:fresh');
    $this->info('Database migrated successful');
    $this->call('db:seed', ['--class' => 'RoleSeeder']);
    $this->info('Role Database seeded successful');
    $this->call('db:seed', ['--class' => 'UserSeeder']);
    $this->info('User Database seeded successful');
})->describe('Seed the admin user into the database');

Artisan::command('bibles:bootup', function () {
    $this->info('Starting Bible and References bootup process...');

    try {
        BootupBiblesAndReferences::dispatchSync();
        $this->info('Bible and References bootup process completed.');
    } catch (\Exception $e) {
        $this->error('Error migrating Bible tables: ' . $e->getMessage());
        return;
    }

    $this->info('Bible and References bootup process completed.');
})->describe('Bootup Bibles and References');