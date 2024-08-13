<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
class CheckAndInstallPassport extends Command
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:check-install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Laravel Passport only if the keys are not generated';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $publicKeyPath = storage_path('oauth-public.key');
        $privateKeyPath = storage_path('oauth-private.key');

        if (!File::exists($publicKeyPath) || !File::exists($privateKeyPath)) {
            $this->info('Passport keys not found. Running passport:install...');
            $this->call('passport:install');
        } else {
            $this->info('Passport keys already exist.');
        }

        return 0;
    }
}
