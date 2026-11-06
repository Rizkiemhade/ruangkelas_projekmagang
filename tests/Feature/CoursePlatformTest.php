<?php

namespace Tests\Feature;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Mail\EnrollmentConfirmation;
use App\Models\Article;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CoursePlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_shows_only_published_courses_and_supports_search_and_category_filter(): void
    {
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $otherCategory = Category::create(['name' => 'Data', 'slug' => 'data']);
        $published = $this->course($category, 'Kelas Laravel');
        $this->course($otherCategory, 'Analisis Data');
        $draft = $this->course($category, 'Kelas Rahasia', CourseStatus::Draft);

        $this->get('/')
            ->assertOk()
            ->assertSee($published->title)
            ->assertSee('css/theme.css')
            ->assertSee('action="'.route('home').'#kelas"', false)
            ->assertDontSee($draft->title);

        $this->get('/?category=web&search=Laravel')
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee('Analisis Data');

        $this->get('/?search%5B%5D=Laravel&category%5B%5D=web')->assertOk();

        $this->get(route('courses.show', $draft))
            ->assertNotFound();
    }

    public function test_public_enrollment_is_unique_per_course_and_email(): void
    {
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $course = $this->course($category);
        $payload = ['name' => 'Alya Rahma', 'email' => '  ALYA@example.com ', 'phone' => '+62 812 1234 5678'];
        Mail::fake();

        $this->post(route('enrollments.store', $course), $payload)
            ->assertRedirect()
            ->assertSessionHas('success', 'Pendaftaran berhasil. Email konfirmasi telah dijadwalkan untuk dikirim ke alamat email Anda.');

        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'email' => 'alya@example.com']);
        Mail::assertQueued(EnrollmentConfirmation::class, fn (EnrollmentConfirmation $mail): bool => $mail->hasTo('alya@example.com')
            && $mail->participantName === 'Alya Rahma'
            && $mail->courseTitle === $course->title);

        $this->post(route('enrollments.store', $course), [
            ...$payload,
            'email' => 'alya@example.com',
        ])->assertSessionHasErrors('email');

        $this->post(route('enrollments.store', $course), [
            ...$payload,
            'email' => ['not-an-email'],
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_enrollment_confirmation_email_explains_the_pending_registration_status(): void
    {
        $mail = new EnrollmentConfirmation('Alya Rahma', 'Kelas Laravel');

        $mail->assertSeeInHtml('Alya Rahma');
        $mail->assertSeeInHtml('Kelas Laravel');
        $mail->assertSeeInHtml('Menunggu');
        $mail->assertSeeInText('Pendaftaran berhasil');
    }

    public function test_admin_authentication_and_dashboard_access_are_restricted(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $admin = User::factory()->create(['email' => 'admin@example.com', 'is_admin' => true]);
        $regularUser = User::factory()->create(['email' => 'member@example.com', 'is_admin' => false]);

        $this->post(route('login.store'), ['email' => $regularUser->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('MENU UTAMA')
            ->assertSee('css/admin.css')
            ->assertSee('css/theme.css');
        $this->post(route('admin.logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_admin_can_update_profile_and_change_password_with_current_password(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Lama',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('Edit profil')
            ->assertSee('admin@example.com');

        $this->put(route('admin.profile.update'), [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@example.com',
            'current_password' => 'password',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
            'is_admin' => false,
        ])->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('success', 'Profil berhasil diperbarui.');

        $admin->refresh();
        $this->assertSame('Admin Baru', $admin->name);
        $this->assertSame('admin.baru@example.com', $admin->email);
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(password_verify('password-baru', $admin->password));

        $this->put(route('admin.profile.update'), [
            'name' => 'Admin Profil Saja',
            'email' => 'admin.baru@example.com',
            'is_admin' => false,
        ])->assertRedirect(route('admin.profile.edit'));

        $admin->refresh();
        $this->assertSame('Admin Profil Saja', $admin->name);
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(password_verify('password-baru', $admin->password));
    }

    public function test_admin_profile_rejects_duplicate_email_and_invalid_password_changes(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com', 'is_admin' => true]);
        User::factory()->create(['email' => 'other@example.com']);
        $this->actingAs($admin);

        $profile = [
            'name' => 'Admin',
            'email' => 'other@example.com',
        ];

        $this->put(route('admin.profile.update'), $profile)
            ->assertSessionHasErrors('email');

        $this->put(route('admin.profile.update'), [
            ...$profile,
            'email' => 'admin@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password');

        $this->put(route('admin.profile.update'), [
            ...$profile,
            'email' => 'admin@example.com',
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password');

        $this->put(route('admin.profile.update'), [
            ...$profile,
            'email' => 'admin@example.com',
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(password_verify('password', $admin->refresh()->password));
    }

    public function test_admin_can_upload_and_replace_profile_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'profile_photo' => UploadedFile::fake()->image('profile.png'),
        ])->assertRedirect(route('admin.profile.edit'));

        $admin->refresh();
        $oldPhotoPath = $admin->profile_photo_path;
        $this->assertNotNull($oldPhotoPath);
        Storage::disk('public')->assertExists($oldPhotoPath);

        $photoUrl = Storage::disk('public')->url($oldPhotoPath);
        $this->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee($photoUrl)
            ->assertSee('Sesuaikan pembingkaian')
            ->assertSee('Ganti foto')
            ->assertDontSee('assets/images/avatar.png');

        $this->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'profile_photo' => UploadedFile::fake()->image('profile-baru.jpg'),
        ])->assertRedirect(route('admin.profile.edit'));

        $admin->refresh();
        $this->assertNotSame($oldPhotoPath, $admin->profile_photo_path);
        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($admin->profile_photo_path);
    }

    public function test_admin_profile_rejects_non_image_uploads_without_changing_the_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create([
            'is_admin' => true,
            'profile_photo_path' => null,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.profile.update'), [
                'name' => $admin->name,
                'email' => $admin->email,
                'profile_photo' => UploadedFile::fake()->create('not-an-image.jpg', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('profile_photo');

        $this->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'profile_photo' => UploadedFile::fake()->image('too-large.png')->size(2049),
        ])->assertSessionHasErrors('profile_photo');

        $this->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'profile_photo' => UploadedFile::fake()->image('too-wide.png', 3001, 1),
        ])->assertSessionHasErrors('profile_photo');

        $this->assertNull($admin->refresh()->profile_photo_path);
        $this->assertSame([], Storage::disk('public')->allFiles('profile-photos'));
    }

    public function test_admin_profile_routes_require_admin_access(): void
    {
        $this->get(route('admin.profile.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.profile.update'), [])->assertRedirect(route('login'));

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)
            ->get(route('admin.profile.edit'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.profile.update'), [])
            ->assertForbidden();
    }

    public function test_admin_can_create_edit_publish_and_delete_articles(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->get(route('admin.articles.index'))
            ->assertOk()
            ->assertSee('Artikel');
        $this->get(route('admin.articles.create'))
            ->assertOk()
            ->assertSee('Tulis artikel');

        $articleData = [
            'title' => 'Belajar Laravel untuk Pemula',
            'excerpt' => 'Panduan awal untuk mulai membangun aplikasi web.',
            'body' => "Mulai dari dasar.\n\nLanjutkan dengan praktik.",
            'author_name' => 'Tim RuangKelas',
            'status' => 'draft',
        ];

        $this->post(route('admin.articles.store'), $articleData)
            ->assertRedirect(route('admin.articles.index'))
            ->assertSessionHas('success', 'Artikel berhasil ditambahkan.');

        $article = Article::where('title', $articleData['title'])->firstOrFail();
        $this->assertSame('belajar-laravel-untuk-pemula', $article->slug);
        $this->assertNull($article->published_at);
        $this->get(route('articles.index'))
            ->assertOk()
            ->assertDontSee($articleData['title']);

        $this->put(route('admin.articles.update', $article), [
            ...$articleData,
            'title' => 'Panduan Laravel Dasar',
            'status' => 'published',
        ])->assertRedirect(route('admin.articles.index'))
            ->assertSessionHas('success', 'Perubahan artikel berhasil disimpan.');

        $article->refresh();
        $this->assertSame('belajar-laravel-untuk-pemula', $article->slug);
        $this->assertNotNull($article->published_at);
        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee('Panduan Laravel Dasar');
        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Mulai dari dasar.')
            ->assertSee('Lanjutkan dengan praktik.');

        $this->delete(route('admin.articles.destroy', $article))
            ->assertRedirect(route('admin.articles.index'))
            ->assertSessionHas('success', 'Artikel berhasil dihapus.');

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_admin_article_creation_generates_a_unique_slug_and_validates_content(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $payload = [
            'title' => 'Belajar Laravel',
            'excerpt' => 'Ringkasan artikel.',
            'body' => 'Isi artikel.',
            'author_name' => 'Admin',
            'status' => 'published',
        ];

        $this->post(route('admin.articles.store'), $payload)->assertRedirect(route('admin.articles.index'));
        $this->post(route('admin.articles.store'), $payload)->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseHas('articles', ['slug' => 'belajar-laravel']);
        $this->assertDatabaseHas('articles', ['slug' => 'belajar-laravel-2']);

        $this->post(route('admin.articles.store'), [
            ...$payload,
            'title' => '',
            'excerpt' => '',
            'body' => '',
            'author_name' => '',
            'status' => 'unexpected',
        ])->assertSessionHasErrors(['title', 'excerpt', 'body', 'author_name', 'status']);
    }

    public function test_admin_article_routes_require_an_admin_account(): void
    {
        $this->get(route('admin.articles.index'))->assertRedirect(route('login'));
        $this->post(route('admin.articles.store'), [])->assertRedirect(route('login'));

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)
            ->get(route('admin.articles.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.articles.store'), [])
            ->assertForbidden();
    }

    public function test_admin_can_create_courses_upload_images_and_update_enrollment_status(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $courseData = [
            'title' => 'Laravel Dasar',
            'description' => 'Belajar membangun aplikasi web.',
            'category_id' => $category->id,
            'instructor' => 'Dina Pratama',
            'price' => '0',
            'status' => CourseStatus::Published->value,
            'image' => UploadedFile::fake()->image('kelas.jpg'),
        ];

        $this->actingAs($admin)
            ->get(route('admin.courses.create'))
            ->assertOk()
            ->assertSee('Tambah kelas')
            ->assertSee('Panel Administrasi RuangKelas');

        $this->actingAs($admin)
            ->post(route('admin.courses.store'), $courseData)
            ->assertRedirect(route('admin.courses.index'));

        $course = Course::where('title', 'Laravel Dasar')->firstOrFail();
        $this->assertNotNull($course->image_path);
        Storage::disk('public')->assertExists($course->image_path);
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'status' => CourseStatus::Published->value]);

        $enrollment = $course->enrollments()->create([
            'name' => 'Alya Rahma',
            'email' => 'alya@example.com',
            'phone' => '+62 812 1234 5678',
        ]);

        $this->get(route('admin.enrollments.index', $course))
            ->assertOk()
            ->assertSee('Aksi')
            ->assertSee('Terima')
            ->assertSee('Tolak');

        $this->patch(route('admin.enrollments.update', [$course, $enrollment]), [
            'status' => EnrollmentStatus::Accepted->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => EnrollmentStatus::Accepted->value,
        ]);

        $this->put(route('admin.courses.update', $course), [
            ...$courseData,
            'title' => 'Laravel Lanjutan',
            'status' => CourseStatus::Draft->value,
        ])->assertRedirect(route('admin.courses.index'));

        $course->refresh();
        $this->assertSame('Laravel Lanjutan', $course->title);
        $this->assertSame(CourseStatus::Draft, $course->status);

        $this->delete(route('admin.courses.destroy', $course))
            ->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
    }

    public function test_admin_can_delete_an_enrollment_only_from_its_own_course(): void
    {
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $course = $this->course($category, 'Kelas Laravel');
        $otherCourse = $this->course($category, 'Kelas Data');
        $enrollment = $course->enrollments()->create([
            'name' => 'Alya Rahma',
            'email' => 'alya@example.com',
            'phone' => '+62 812 1234 5678',
        ]);
        $otherEnrollment = $otherCourse->enrollments()->create([
            'name' => 'Dimas Putra',
            'email' => 'dimas@example.com',
            'phone' => '+62 812 1111 2222',
        ]);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->delete(route('admin.enrollments.destroy', [$course, $enrollment]))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id]);

        $this->actingAs($admin)
            ->get(route('admin.enrollments.index', $course))
            ->assertOk()
            ->assertSee('Hapus')
            ->assertSee('Hapus peserta ini secara permanen? Tindakan ini tidak dapat dibatalkan.');

        $this->actingAs($admin)
            ->delete(route('admin.enrollments.destroy', [$course, $enrollment]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Data peserta berhasil dihapus.');

        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);

        $this->actingAs($admin)
            ->delete(route('admin.enrollments.destroy', [$course, $otherEnrollment]))
            ->assertNotFound();

        $this->assertDatabaseHas('enrollments', ['id' => $otherEnrollment->id]);
    }

    public function test_admin_action_buttons_are_grouped_in_dropdowns_across_management_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $course = $this->course($category, 'Kelas Laravel');
        $enrollment = $course->enrollments()->create([
            'name' => 'Alya Rahma',
            'email' => 'alya@example.com',
            'phone' => '+62 812 1234 5678',
        ]);
        $article = Article::create([
            'title' => 'Belajar Laravel',
            'slug' => 'belajar-laravel',
            'excerpt' => 'Ringkasan artikel.',
            'body' => 'Isi artikel.',
            'author_name' => 'Tim RuangKelas',
            'published_at' => now(),
        ]);
        $this->actingAs($admin);

        $this->get(route('admin.courses.index'))
            ->assertOk()
            ->assertSee('aria-label="Aksi kelas: Kelas Laravel"', false)
            ->assertSee(route('admin.courses.edit', $course), false)
            ->assertSee(route('admin.courses.destroy', $course), false);

        $this->get(route('admin.enrollments.index', $course))
            ->assertOk()
            ->assertSee('aria-label="Aksi pendaftar: Alya Rahma"', false)
            ->assertSee('Terima')
            ->assertSee('Tolak')
            ->assertSee(route('admin.enrollments.update', [$course, $enrollment]), false)
            ->assertSee(route('admin.enrollments.destroy', [$course, $enrollment]), false);

        $this->get(route('admin.enrollments.all'))
            ->assertOk()
            ->assertSee('aria-label="Aksi pendaftar: Alya Rahma"', false)
            ->assertSee(route('admin.enrollments.update', [$course, $enrollment]), false);

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('aria-label="Aksi kategori: Web"', false)
            ->assertSee(route('admin.categories.edit', $category), false)
            ->assertSee(route('admin.categories.destroy', $category), false);

        $this->get(route('admin.articles.index'))
            ->assertOk()
            ->assertSee('aria-label="Aksi artikel: Belajar Laravel"', false)
            ->assertSee(route('admin.articles.edit', $article), false)
            ->assertSee(route('articles.show', $article), false)
            ->assertSee(route('admin.articles.destroy', $article), false);
    }

    public function test_admin_can_view_all_enrollments_from_a_separate_sidebar_page(): void
    {
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $course = $this->course($category, 'Kelas Laravel');
        $otherCourse = $this->course($category, 'Kelas Data');
        $course->enrollments()->create([
            'name' => 'Alya Rahma',
            'email' => 'alya@example.com',
            'phone' => '+62 812 1234 5678',
        ]);
        $otherCourse->enrollments()->create([
            'name' => 'Dimas Putra',
            'email' => 'dimas@example.com',
            'phone' => '+62 812 1111 2222',
        ]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->get(route('admin.enrollments.all'))
            ->assertRedirect(route('login'));

        $this->actingAs($admin)
            ->get(route('admin.enrollments.all'))
            ->assertOk()
            ->assertSee('Semua pendaftar')
            ->assertSee('Alya Rahma')
            ->assertSee('Dimas Putra')
            ->assertSee('Kelas Laravel')
            ->assertSee('Kelas Data')
            ->assertSee('Kategori')
            ->assertSee('Pendaftar');
    }

    public function test_admin_can_search_enrollments_by_participant_details_and_course(): void
    {
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $laravelCourse = $this->course($category, 'Kelas Laravel');
        $dataCourse = $this->course($category, 'Kelas Data');
        $laravelEnrollment = $laravelCourse->enrollments()->create([
            'name' => 'Alya Rahma',
            'email' => 'alya@example.com',
            'phone' => '+62 812 1234 5678',
        ]);
        $dataEnrollment = $dataCourse->enrollments()->create([
            'name' => 'Dimas Putra',
            'email' => 'dimas@example.com',
            'phone' => '+62 812 9876 5432',
        ]);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->get(route('admin.enrollments.all', ['search' => 'Alya']))
            ->assertOk()
            ->assertSee('Alya Rahma')
            ->assertDontSee('Dimas Putra');

        $this->get(route('admin.enrollments.all', ['search' => 'Kelas Data']))
            ->assertOk()
            ->assertSee('Dimas Putra')
            ->assertDontSee('Alya Rahma');

        $this->get(route('admin.enrollments.index', [$laravelCourse, 'search' => 'alya@example.com']))
            ->assertOk()
            ->assertSee('Alya Rahma')
            ->assertDontSee('Dimas Putra');

        $this->get(route('admin.enrollments.index', [$laravelCourse, 'search' => '1234 5678']))
            ->assertOk()
            ->assertSee('Alya Rahma')
            ->assertDontSee('Dimas Putra');

        $this->get(route('admin.enrollments.all', ['search' => ['malformed']]))
            ->assertOk()
            ->assertSee('Alya Rahma')
            ->assertSee('Dimas Putra');
    }

    public function test_admin_can_manage_categories_and_cannot_delete_a_category_in_use(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->get(route('admin.categories.create'))
            ->assertOk()
            ->assertSee('Tambah kategori')
            ->assertSee('Panel Administrasi RuangKelas');

        $this->post(route('admin.categories.store'), [
            'name' => 'Pengembangan Web',
            'description' => 'Kelas pengembangan aplikasi web.',
        ])->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', 'Pengembangan Web')->firstOrFail();
        $this->put(route('admin.categories.update', $category), [
            'name' => 'Web Engineering',
            'description' => 'Kelas pengembangan aplikasi web modern.',
        ])->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertSame('Web Engineering', $category->name);

        $course = $this->course($category);
        $this->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        $course->delete();
        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_database_seeder_creates_the_demo_admin_and_sample_catalog(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@ruangkelas.test',
            'is_admin' => true,
        ]);
        $this->assertDatabaseCount('categories', 4);
        $this->assertDatabaseCount('courses', 6);
        $this->assertDatabaseCount('enrollments', 3);
        $this->assertDatabaseCount('articles', 3);

        $this->post(route('login.store'), [
            'email' => 'admin@ruangkelas.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_course_search_and_admin_course_list_are_paginated(): void
    {
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);

        foreach (range(1, 11) as $number) {
            $this->course($category, "Kelas {$number}");
        }

        $this->get('/?search=Kelas')
            ->assertOk()
            ->assertSee('page=2');

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)
            ->get(route('admin.courses.index', ['search' => 'Kelas']))
            ->assertOk()
            ->assertSee('page=2');

        $this->get(route('admin.courses.index', ['search' => ['malformed']]))->assertOk();
    }

    private function course(Category $category, string $title = 'Kelas Digital', CourseStatus $status = CourseStatus::Published): Course
    {
        return Course::create([
            'category_id' => $category->id,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'description' => 'Deskripsi kelas untuk pengujian.',
            'instructor' => 'Instruktur',
            'price' => 0,
            'status' => $status,
        ]);
    }
}
