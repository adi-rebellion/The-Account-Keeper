<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CreateDatabase extends Command
{
    protected $signature = 'db:create {database?}';
    protected $description = 'Create a new database if it does not exist';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $database = $this->argument('database') ?? env('DB_DATABASE', 'forge');
        $charset = env('DB_CHARSET', 'utf8mb4');
        $collation = env('DB_COLLATION', 'utf8mb4_unicode_ci');

        $query = "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET $charset COLLATE $collation;";

        // Set the default database connection to null
        Config::set('database.connections.temp', array_merge(
            Config::get('database.connections.mysql'),
            ['database' => null]
        ));

        // Use the temporary connection to run the statement
        DB::connection('temp')->statement($query);

        $this->info("Database '$database' created successfully or already exists.");
    }
}
