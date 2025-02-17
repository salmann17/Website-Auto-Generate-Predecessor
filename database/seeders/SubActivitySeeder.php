<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sub_activity')->insert([
            ['idsub_activity' => 1, 'activity' => 'Pekerjaan Persiapan', 'idactivity' => 1],
            ['idsub_activity' => 2, 'activity' => 'Pekerjaan Pondasi Bored Pile dia. 60cm', 'idactivity' => 2],
            ['idsub_activity' => 3, 'activity' => 'Pekerjaan Pondasi Pile Cap PC 3', 'idactivity' => 3],
            ['idsub_activity' => 4, 'activity' => 'Pekerjaan Pondasi Pile Cap PC 4', 'idactivity' => 3],
            ['idsub_activity' => 5, 'activity' => 'Pekerjaan Pondasi Pile Cap PC 5', 'idactivity' => 3],
            ['idsub_activity' => 6, 'activity' => 'Pekerjaan Pondasi Pile Cap PC 6', 'idactivity' => 3],
            ['idsub_activity' => 7, 'activity' => 'Sloof TB2565 - 1', 'idactivity' => 4],
            ['idsub_activity' => 8, 'activity' => 'Sloof TB375 - 1', 'idactivity' => 4],
            ['idsub_activity' => 9, 'activity' => 'Sloof TB375 - 2', 'idactivity' => 4],
            ['idsub_activity' => 10, 'activity' => 'Sloof TBK374 - 1', 'idactivity' => 4],
            ['idsub_activity' => 11, 'activity' => 'Pekerjaan Awal', 'idactivity' => 5],
            ['idsub_activity' => 12, 'activity' => 'Pekerjaan Struktur', 'idactivity' => 5],
            ['idsub_activity' => 13, 'activity' => 'Pekerjaan Lain Lain', 'idactivity' => 5],
        ]);
    }
}
