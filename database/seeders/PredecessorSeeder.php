<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PredecessorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('predecessor')->insert([
            ['node_core' => 2, 'node_cabang' => 1],
            ['node_core' => 3, 'node_cabang' => 2],
            ['node_core' => 4, 'node_cabang' => 3],
            ['node_core' => 4, 'node_cabang' => 2],
            ['node_core' => 5, 'node_cabang' => 4],
            ['node_core' => 7, 'node_cabang' => 6],
            ['node_core' => 8, 'node_cabang' => 7],
            ['node_core' => 9, 'node_cabang' => 6],
            ['node_core' => 9, 'node_cabang' => 7],
            ['node_core' => 9, 'node_cabang' => 8],
        ]);
    }
}
