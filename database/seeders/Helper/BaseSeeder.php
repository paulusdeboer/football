<?php

namespace Database\Seeders\Helper;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BaseSeeder extends Seeder
{
    use ChunkTrait;

    /**
     * Faker service
     *
     * @var \Faker\Generator
     */
    protected $fakerService;

    /**
     * HelperSeeder constructor.
     */
    public function __construct()
    {
        $this->fakerService = Factory::create();
    }

    /**
     * Run the seeder and add data to the database
     */
    public function run(): void
    {
        DB::connection()->disableQueryLog();

        $this->execute();

        $this->seed();

        DB::connection()->enableQueryLog();
    }

    /**
     * Execute the seeder
     */
    public function execute(): void
    {
        // Implement this method in your seeder
    }
}
