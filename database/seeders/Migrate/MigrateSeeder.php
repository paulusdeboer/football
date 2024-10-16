<?php

namespace Database\Seeders\Migrate;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param ConnectionInterface $connection
     * @return void
     */
    public function run(ConnectionInterface $connection)
    {
        DB::connection()->disableQueryLog();

        $this->call(
            [
                PlayersMigrateSeeder::class,
//                TeamsMigrateSeeder::class,
//                GamesMigrateSeeder::class,
//                RatingsMigrateSeeder::class,
            ],
            false,
            [
                $connection,
            ]
        );

        DB::connection()->enableQueryLog();
    }
}
