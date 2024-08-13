<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('createenv', function () {
    $envPath = base_path('.env');
    $envExamplePath = base_path('.env.example');

    if (!File::exists($envPath) && File::exists($envExamplePath)) {
        $this->info('.env file not found. Copying .env.example to .env...');
        File::copy($envExamplePath, $envPath);
    } else {
        $this->info('.env already exists.');
    }
})->purpose('Create a new enviroment file if not present.');

Artisan::command('createpassport', function () {
    $publicKeyPath = storage_path('oauth-public.key');
    $privateKeyPath = storage_path('oauth-private.key');

    if (!File::exists($publicKeyPath) || !File::exists($privateKeyPath)) {

        $this->info('Passport keys not found. Running passport:install...');
        $this->call('passport:install');
    } else {
        File::delete($publicKeyPath);
        File::delete($privateKeyPath);
        $this->call('passport:install');
    }
})->purpose('Create a new enviroment file if not present.');
