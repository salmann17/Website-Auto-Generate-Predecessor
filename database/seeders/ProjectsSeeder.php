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
        DB::table('projects')->insert([
            ['id' => 1, 'nama' => 'Project Alpha', 'alamat' => 'Jl. Merdeka No. 1', 'deskripsi' => 'Proyek pembangunan gedung perkantoran dengan desain ramah lingkungan dan sistem canggih.'],
            ['id' => 2, 'nama' => 'Project Beta', 'alamat' => 'Jl. Sudirman No. 2', 'deskripsi' => 'Pembangunan pusat perbelanjaan yang dilengkapi dengan fasilitas hiburan dan parkir luas.'],
            ['id' => 3, 'nama' => 'Project Gamma', 'alamat' => 'Jl. Gajah Mada No. 3', 'deskripsi' => 'Proyek pembangunan kompleks perumahan modern dengan fasilitas umum dan area hijau.'],
            ['id' => 4, 'nama' => 'Project Delta', 'alamat' => 'Jl. Pahlawan No. 4', 'deskripsi' => 'Pembangunan kampus universitas dengan laboratorium dan fasilitas olahraga modern.'],
            ['id' => 5, 'nama' => 'Project Epsilon', 'alamat' => 'Jl. Melati No. 5', 'deskripsi' => 'Pembangunan rumah sakit dengan ruang ICU dan fasilitas lengkap lainnya.'],
            ['id' => 6, 'nama' => 'Project Zeta', 'alamat' => 'Jl. Kuningan No. 6', 'deskripsi' => 'Pembangunan gedung apartemen dengan area komersial dan fasilitas kebugaran.'],
            ['id' => 7, 'nama' => 'Project Eta', 'alamat' => 'Jl. Karya No. 7', 'deskripsi' => 'Proyek pembangunan kawasan industri dengan sistem logistik dan pengelolaan canggih.'],
            ['id' => 8, 'nama' => 'Project Theta', 'alamat' => 'Jl. Merpati No. 8', 'deskripsi' => 'Pembangunan gedung perkantoran untuk startup dan pusat riset teknologi.'],
            ['id' => 9, 'nama' => 'Project Iota', 'alamat' => 'Jl. Raya No. 9', 'deskripsi' => 'Pembangunan pusat konferensi internasional dengan ruang pertemuan besar.'],
            ['id' => 10, 'nama' => 'Project Kappa', 'alamat' => 'Jl. Jendral Sudirman No. 10', 'deskripsi' => 'Pembangunan kawasan rekreasi keluarga dengan taman bermain dan kolam renang.'],
            ['id' => 11, 'nama' => 'Project Lambda', 'alamat' => 'Jl. Sejahtera No. 11', 'deskripsi' => 'Pembangunan fasilitas olahraga profesional dengan lapangan tenis dan stadion.'],
            ['id' => 12, 'nama' => 'Project Mu', 'alamat' => 'Jl. Bukit No. 12', 'deskripsi' => 'Pembangunan stasiun transportasi massal dengan kapasitas penumpang tinggi.'],
            ['id' => 13, 'nama' => 'Project Nu', 'alamat' => 'Jl. Cendana No. 13', 'deskripsi' => 'Pembangunan apartemen mewah dengan fasilitas spa dan pusat kebugaran.'],
            ['id' => 14, 'nama' => 'Project Xi', 'alamat' => 'Jl. Anggrek No. 14', 'deskripsi' => 'Pembangunan perumahan elit dengan konsep ramah lingkungan dan taman terbuka.'],
            ['id' => 15, 'nama' => 'Project Omicron', 'alamat' => 'Jl. Raya No. 15', 'deskripsi' => 'Pembangunan pusat perbelanjaan dengan kantor pusat perusahaan besar.'],
        ]);
    }
}
