<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ruangkelas.test'],
            [
                'name' => 'Admin RuangKelas',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );

        $categoryData = [
            'web-development' => ['Pengembangan Web', 'Bangun aplikasi web modern dari dasar hingga siap dipublikasikan.'],
            'data' => ['Data & AI', 'Pelajari cara mengolah data dan membangun solusi berbasis AI.'],
            'design' => ['UI/UX Design', 'Rancang pengalaman digital yang mudah digunakan dan menarik.'],
            'career' => ['Karier Teknologi', 'Persiapkan diri untuk memulai atau mengembangkan karier di bidang teknologi.'],
        ];

        $categories = [];

        foreach ($categoryData as $slug => [$name, $description]) {
            $categories[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description],
            );
        }

        $courseData = [
            ['laravel-fundamental', 'Laravel dari Nol', 'Bangun aplikasi web dengan routing, Blade, Eloquent, autentikasi, dan praktik terbaik Laravel.', 'Dina Pratama', 0, 'web-development'],
            ['frontend-modern', 'Frontend Modern dengan React', 'Pelajari komponen, state, dan integrasi API untuk membangun antarmuka web yang responsif.', 'Rafi Nugraha', 249000, 'web-development'],
            ['python-data', 'Python untuk Analisis Data', 'Mulai menganalisis data menggunakan Python, Pandas, dan visualisasi yang informatif.', 'Maya Putri', 199000, 'data'],
            ['ui-ux-praktis', 'UI/UX Design Praktis', 'Pahami riset pengguna, wireframe, prototipe, dan evaluasi desain untuk produk digital.', 'Nadia Sari', 179000, 'design'],
            ['sql-untuk-pemula', 'SQL untuk Pemula', 'Kuasai query, relasi tabel, dan cara mengambil insight dari database relasional.', 'Bayu Santoso', 0, 'data'],
            ['siap-karier-digital', 'Siap Berkarier di Dunia Digital', 'Susun portofolio, CV, dan strategi wawancara untuk pekerjaan teknologi pertamamu.', 'Intan Lestari', 99000, 'career'],
        ];

        $courses = [];

        foreach ($courseData as [$slug, $title, $description, $instructor, $price, $categorySlug]) {
            $courses[$slug] = Course::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $categories[$categorySlug]->id,
                    'title' => $title,
                    'description' => $description,
                    'instructor' => $instructor,
                    'price' => $price,
                    'status' => CourseStatus::Published,
                ],
            );
        }

        $enrollmentData = [
            ['laravel-fundamental', 'Alya Rahma', 'alya@example.com', '+62 812 0000 0001', EnrollmentStatus::Pending],
            ['frontend-modern', 'Dimas Putra', 'dimas@example.com', '+62 812 0000 0002', EnrollmentStatus::Accepted],
            ['python-data', 'Siti Amelia', 'siti@example.com', '+62 812 0000 0003', EnrollmentStatus::Pending],
        ];

        foreach ($enrollmentData as [$courseSlug, $name, $email, $phone, $status]) {
            Enrollment::firstOrCreate(
                ['course_id' => $courses[$courseSlug]->id, 'email' => $email],
                ['name' => $name, 'phone' => $phone, 'status' => $status],
            );
        }

        $this->call(ArticleSeeder::class);
    }
}
