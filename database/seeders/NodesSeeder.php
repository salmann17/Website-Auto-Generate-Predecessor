<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('nodes')->insert([
            ['id' => 1, 'activity' => 'Penyusunan Desain', 'durasi' => 7, 'prioritas' => 1, 'project_idproject' => 1],
            ['id' => 2, 'activity' => 'Pembangunan Struktur Utama', 'durasi' => 15, 'prioritas' => 2, 'project_idproject' => 1],
            ['id' => 3, 'activity' => 'Pemasangan Sistem Mekanikal', 'durasi' => 10, 'prioritas' => 3, 'project_idproject' => 1],
            ['id' => 4, 'activity' => 'Pemasangan Elektrikal', 'durasi' => 8, 'prioritas' => 4, 'project_idproject' => 1],
            ['id' => 5, 'activity' => 'Penyelesaian Interior', 'durasi' => 5, 'prioritas' => 5, 'project_idproject' => 1],
            ['id' => 6, 'activity' => 'Penyusunan Desain', 'durasi' => 5, 'prioritas' => 1, 'project_idproject' => 2],
            ['id' => 7, 'activity' => 'Pembangunan Pondasi', 'durasi' => 10, 'prioritas' => 2, 'project_idproject' => 2],
            ['id' => 8, 'activity' => 'Pemasangan Pipa dan Sanitasi', 'durasi' => 12, 'prioritas' => 3, 'project_idproject' => 2],
            ['id' => 9, 'activity' => 'Pemasangan Dinding', 'durasi' => 10, 'prioritas' => 4, 'project_idproject' => 2],
        ]);
    }
}
