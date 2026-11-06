<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articleData = [
            [
                'slug' => 'mulai-belajar-programming-dengan-proyek-kecil',
                'title' => 'Mulai Belajar Programming dengan Proyek Kecil',
                'excerpt' => 'Proyek sederhana membantu pemula memahami konsep pemrograman sekaligus membangun kebiasaan belajar yang konsisten.',
                'body' => "Belajar programming terasa lebih mudah ketika teori langsung dipraktikkan. Mulailah dari proyek kecil yang punya tujuan jelas, seperti daftar tugas, halaman profil, atau kalkulator sederhana.\n\nPilih satu konsep baru untuk setiap proyek. Setelah berhasil, rapikan kode dan catat hal yang kamu pelajari. Portofolio kecil yang terus berkembang menunjukkan proses belajar yang nyata.",
                'author_name' => 'Tim RuangKelas',
                'published_at' => now()->subDays(3),
            ],
            [
                'slug' => 'cara-memilih-kelas-online-yang-tepat',
                'title' => 'Cara Memilih Kelas Online yang Tepat',
                'excerpt' => 'Kenali tujuan belajar, cek materi, dan pilih format kelas yang sesuai dengan waktu serta pengalamanmu.',
                'body' => "Tentukan keterampilan yang ingin kamu kuasai dan hasil yang ingin dicapai. Cocokkan tujuan tersebut dengan materi, tingkat kelas, serta proyek praktik yang ditawarkan.\n\nPeriksa juga pengalaman instruktur dan estimasi waktu belajar. Kelas yang tepat bukan hanya menarik, tetapi dapat diselesaikan secara konsisten di tengah rutinitas.",
                'author_name' => 'Dina Pratama',
                'published_at' => now()->subDays(6),
            ],
            [
                'slug' => 'membangun-portofolio-digital-pertamamu',
                'title' => 'Membangun Portofolio Digital Pertamamu',
                'excerpt' => 'Dokumentasikan proses, keputusan, dan hasil proyek untuk membuat portofolio yang lebih meyakinkan.',
                'body' => "Portofolio yang baik menceritakan cara kamu menyelesaikan masalah, bukan sekadar menampilkan hasil akhir. Jelaskan konteks, peranmu, pilihan yang dibuat, dan apa yang kamu pelajari.\n\nMulai dengan satu proyek yang paling relevan. Pastikan tautan dapat dibuka dan penjelasannya ringkas, lalu perbarui portofolio setiap kali kemampuanmu bertambah.",
                'author_name' => 'Maya Putri',
                'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($articleData as $article) {
            Article::updateOrCreate(['slug' => $article['slug']], $article);
        }
    }
}
