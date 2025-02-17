<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('activity')->insert([
            ['idactivity' => 1, 'activity' => 'Pekerjaan Persiapan', 'idproject' => 1],
            ['idactivity' => 2, 'activity' => 'Pekerjaan Pondasi Bored Pile dia. 60cm; H=31m', 'idproject' => 1],
            ['idactivity' => 3, 'activity' => 'Pekerjaan Pondasi Pile Cap', 'idproject' => 1],
            ['idactivity' => 4, 'activity' => 'Pekerjaan Pondasi Sloof', 'idproject' => 1],
            ['idactivity' => 5, 'activity' => 'Pekerjaan Tambahan', 'idproject' => 1],
        ]);
    }
}
