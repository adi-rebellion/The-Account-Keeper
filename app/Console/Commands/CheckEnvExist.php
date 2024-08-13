<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;

class CheckEnvExist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:check-exist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating .env file if not exists';

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
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        if (!File::exists($envPath) && File::exists($envExamplePath)) {
            $this->info('.env file not found. Copying .env.example to .env...');
            File::copy($envExamplePath, $envPath);
        }else{
            $this->info('.env already exists.');
        }
    }
}
