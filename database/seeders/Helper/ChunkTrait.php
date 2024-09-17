<?php

namespace Database\Seeders\Helper;

use Illuminate\Support\Facades\DB;

trait ChunkTrait
{
    /**
     * Multi-dimensional array to store data
     *
     * @var array
     */
    private $data = [];

    /**
     * Add data to the data array
     */
    protected function add(array $data, string $table): void
    {
        $this->data[$table][] = $data;
    }

    /**
     * Begin the insertion of data
     */
    protected function seed(): void
    {
        foreach ($this->data as $table => $data) {
            $this->insert($data, $table);
        }
    }

    /**
     * Insert data into a table
     */
    private function insert(array $data, string $table, int $amount = 500): void
    {
        if (class_exists($table)) {
            $this->insertByClass($data, $table, $amount);
        } else {
            $this->insertByTable($data, $table, $amount);
        }
    }

    /**
     * Insert data into a table using Eloquent
     */
    private function insertByClass(array $data, string $class, int $amount = 500): void
    {
        $chunks = array_chunk($data, $amount);
        foreach ($chunks as $chunk) {
            $class::insert($chunk);
        }
    }

    /**
     * Insert data into a table using DB::table
     */
    private function insertByTable(array $data, string $table, int $amount = 500): void
    {
        $chunks = array_chunk($data, $amount);
        foreach ($chunks as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }
}
