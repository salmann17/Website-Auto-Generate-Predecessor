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
            ['idnode' => 1, 'activity' => 'MOB demo alat berat dan persiapan laha', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 1],

            ['idnode' => 2, 'activity' => 'Pengeboran diameter 60cm', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 2],
            ['idnode' => 3, 'activity' => 'Pengecoran beton fc30', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 2],
            ['idnode' => 4, 'activity' => 'Pengecoran dengan menggunakan pipa tremie slump', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 2],

            ['idnode' => 5, 'activity' => 'Pekerjaan galian tanah', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 3],
            ['idnode' => 6, 'activity' => 'Pekerjaan urungan kembali', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 3],
            ['idnode' => 7, 'activity' => 'Pekerjaan cor beton', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 3],
            ['idnode' => 8, 'activity' => 'Pekerjaan bekisting', 'durasi' => 7, 'prioritas' => 4, 'id_sub_activity' => 3],
            ['idnode' => 9, 'activity' => 'Pembesian tulangan pile cap', 'durasi' => 7, 'prioritas' => 5, 'id_sub_activity' => 3],

            ['idnode' => 10, 'activity' => 'Pekerjaan galian tanah', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 4],
            ['idnode' => 11, 'activity' => 'Pekerjaan urungan kembali', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 4],
            ['idnode' => 12, 'activity' => 'Pekerjaan cor beton', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 4],
            ['idnode' => 13, 'activity' => 'Pekerjaan bekisting', 'durasi' => 7, 'prioritas' => 4, 'id_sub_activity' => 4],
            ['idnode' => 14, 'activity' => 'Pembesian tulangan pile cap', 'durasi' => 7, 'prioritas' => 5, 'id_sub_activity' => 4],

            ['idnode' => 15, 'activity' => 'Pekerjaan galian tanah', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 5],
            ['idnode' => 16, 'activity' => 'Pekerjaan urungan kembali', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 5],
            ['idnode' => 17, 'activity' => 'Pekerjaan cor beton', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 5],
            ['idnode' => 18, 'activity' => 'Pekerjaan bekisting', 'durasi' => 7, 'prioritas' => 4, 'id_sub_activity' => 5],
            ['idnode' => 19, 'activity' => 'Pembesian tulangan pile cap', 'durasi' => 7, 'prioritas' => 5, 'id_sub_activity' => 5],

            ['idnode' => 20, 'activity' => 'Pekerjaan galian tanah', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 6],
            ['idnode' => 21, 'activity' => 'Pekerjaan urungan kembali', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 6],
            ['idnode' => 22, 'activity' => 'Pekerjaan cor beton', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 6],
            ['idnode' => 23, 'activity' => 'Pekerjaan bekisting', 'durasi' => 7, 'prioritas' => 4, 'id_sub_activity' => 6],
            ['idnode' => 24, 'activity' => 'Pembesian tulangan pile cap', 'durasi' => 7, 'prioritas' => 5, 'id_sub_activity' => 6],

            ['idnode' => 25, 'activity' => 'Pekerjaan galian tanah', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 7],
            ['idnode' => 26, 'activity' => 'Pekerjaan urungan kembali', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 7],
            ['idnode' => 27, 'activity' => 'Pekerjaan cor beton', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 7],
            ['idnode' => 28, 'activity' => 'Pekerjaan bekisting', 'durasi' => 7, 'prioritas' => 4, 'id_sub_activity' => 7],
            ['idnode' => 29, 'activity' => 'Pembesian tulangan pile cap', 'durasi' => 7, 'prioritas' => 5, 'id_sub_activity' => 7],

            ['idnode' => 30, 'activity' => 'Pekerjaan galian tanah', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 8],
            ['idnode' => 31, 'activity' => 'Pekerjaan urungan kembali', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 8],
            ['idnode' => 32, 'activity' => 'Pekerjaan cor beton', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 8],
            ['idnode' => 33, 'activity' => 'Pekerjaan bekisting', 'durasi' => 7, 'prioritas' => 4, 'id_sub_activity' => 8],
            ['idnode' => 34, 'activity' => 'Pembesian tulangan pile cap', 'durasi' => 7, 'prioritas' => 5, 'id_sub_activity' => 8],

            ['idnode' => 35, 'activity' => 'Pekerjaan galian tanah', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 9],
            ['idnode' => 36, 'activity' => 'Pekerjaan urungan kembali', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 9],
            ['idnode' => 37, 'activity' => 'Pekerjaan cor beton', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 9],
            ['idnode' => 38, 'activity' => 'Pekerjaan bekisting', 'durasi' => 7, 'prioritas' => 4, 'id_sub_activity' => 9],
            ['idnode' => 39, 'activity' => 'Pembesian tulangan pile cap', 'durasi' => 7, 'prioritas' => 5, 'id_sub_activity' => 9],

            ['idnode' => 40, 'activity' => 'Pekerjaan galian tanah', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 10],
            ['idnode' => 41, 'activity' => 'Pekerjaan urungan kembali', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 10],
            ['idnode' => 42, 'activity' => 'Pekerjaan cor beton', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 10],
            ['idnode' => 43, 'activity' => 'Pekerjaan bekisting', 'durasi' => 7, 'prioritas' => 4, 'id_sub_activity' => 10],
            ['idnode' => 44, 'activity' => 'Pembesian tulangan pile cap', 'durasi' => 7, 'prioritas' => 5, 'id_sub_activity' => 10],

            ['idnode' => 45, 'activity' => 'Asuransi CAR', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 11],

            ['idnode' => 46, 'activity' => 'SLT Test', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 12],
            ['idnode' => 47, 'activity' => 'PDA Test', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 12],
            ['idnode' => 48, 'activity' => 'Lateral Test', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 12],
            ['idnode' => 49, 'activity' => 'Axial Tension Test', 'durasi' => 7, 'prioritas' => 4, 'id_sub_activity' => 12],

            ['idnode' => 50, 'activity' => 'Bobok kepala tiang pancang 25x25 ', 'durasi' => 7, 'prioritas' => 1, 'id_sub_activity' => 13],
            ['idnode' => 51, 'activity' => 'Bobok kepala borepile dia.60cm', 'durasi' => 7, 'prioritas' => 2, 'id_sub_activity' => 13],
            ['idnode' => 52, 'activity' => 'Buang tanah bekas galian', 'durasi' => 7, 'prioritas' => 3, 'id_sub_activity' => 13],
            ['idnode' => 53, 'activity' => 'Lantai kerja tebal 5cm', 'durasi' => 7, 'prioritas' => 4, 'id_sub_activity' => 13],
            ['idnode' => 54, 'activity' => 'Lapisan pasir urug 10cm', 'durasi' => 7, 'prioritas' => 5, 'id_sub_activity' => 13],
            ['idnode' => 55, 'activity' => 'Bongkar paving + pasang kembali', 'durasi' => 7, 'prioritas' => 6, 'id_sub_activity' => 13],

        ]);
    }
}
