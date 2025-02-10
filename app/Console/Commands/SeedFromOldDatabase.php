<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Migrate\MigrateSeeder;

class SeedFromOldDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:from-old-db {database} {--driver=} {--host=} {--username=} {--password=} {--charset=} {--collation=} {--prefix=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports data from an old application with a different schema';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Connecting to the old database...');

        $default = DB::getDefaultConnection();
        config(['database.connections.old_db' => [
            'driver' => $this->option('driver') ?: config("database.connections.$default.driver"),
            'host' => $this->option('host') ?: config("database.connections.$default.host"),
            'database' => $this->argument('database') ?: config("database.connections.$default.database"),
            'username' => $this->option('username') ?: config("database.connections.$default.username"),
            'password' => $this->option('password') ?: config("database.connections.$default.password"),
            'charset' => $this->option('charset') ?: config("database.connections.$default.charset"),
            'collation' => $this->option('collation') ?: config("database.connections.$default.collation"),
            'prefix' => $this->option('prefix') ?: config("database.connections.$default.prefix"),
        ]]);

        $connection = DB::connection('old_db');

        $this->info('Starting data migration...');
        (new MigrateSeeder())->setCommand($this)->run($connection);

        $this->info('Data migration completed successfully.');

        return 0;
    }
}
