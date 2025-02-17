<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('project')->insert([
            ['idproject' => 1, 'nama' => 'Project Kantor Bandeng - Struktur Bawah', 'alamat' => 'Jl. Bandeng No. 1', 'deskripsi' => 'Proyek pembangunan gedung perkantoran dengan desain ramah lingkungan dan sistem canggih.'],
            ['idproject' => 2, 'nama' => 'Project Kantor Perak Barat', 'alamat' => 'Jl. Perak No. 2', 'deskripsi' => 'Pembangunan pusat perbelanjaan yang dilengkapi dengan fasilitas hiburan dan parkir luas.'],
            ['idproject' => 3, 'nama' => 'Project Kantor Kalianak', 'alamat' => 'Jl. Kalianak No. 3', 'deskripsi' => 'Proyek pembangunan kompleks perumahan modern dengan fasilitas umum dan area hijau.'],
            ['idproject' => 4, 'nama' => 'Project Kantor Karet', 'alamat' => 'Jl. Karet No. 4', 'deskripsi' => 'Pembangunan kampus universitas dengan laboratorium dan fasilitas olahraga modern.'],
            ['idproject' => 5, 'nama' => 'Project Kantor Balikpapan', 'alamat' => 'Jl. Balikpapan No. 5', 'deskripsi' => 'Pembangunan rumah sakit dengan ruang ICU dan fasilitas lengkap lainnya.'],
        ]);
    }
}
