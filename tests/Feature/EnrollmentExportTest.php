<?php

namespace Tests\Feature;

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnrollmentExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_all_enrollments_for_one_course_as_formula_safe_csv(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $course = $this->course($category, 'Kelas CSV');
        $otherCourse = $this->course($category, 'Kelas Lain');

        foreach (range(1, 16) as $number) {
            $course->enrollments()->create([
                'name' => "Peserta {$number}",
                'email' => "peserta{$number}@example.com",
                'phone' => '+62 812 0000 '.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
            ]);
        }

        $course->enrollments()->firstOrFail()->update(['name' => '=HYPERLINK("https://example.com","klik")']);
        $otherCourse->enrollments()->create([
            'name' => 'Peserta kelas lain',
            'email' => 'peserta-lain@example.com',
            'phone' => '+62 812 0000 0000',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.enrollments.export', $course));
        $response->assertDownload('pendaftar-kelas-csv.csv')
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString("'=HYPERLINK(", $csv);
        $this->assertStringContainsString('Peserta 16', $csv);
        $this->assertStringNotContainsString('Peserta kelas lain', $csv);
    }

    public function test_admin_can_export_all_enrollments_across_courses_in_one_csv(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $firstCourse = $this->course($category, 'Kelas Pertama');
        $secondCourse = $this->course($category, 'Kelas Kedua');

        $firstCourse->enrollments()->create([
            'name' => 'Alya Rahma',
            'email' => 'alya@example.com',
            'phone' => '+62 812 0000 0001',
        ]);
        $secondCourse->enrollments()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '+62 812 0000 0002',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.enrollments.export-all'));
        $response->assertDownload('pendaftar-semua-kelas.csv')
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Alya Rahma', $csv);
        $this->assertStringContainsString('Budi Santoso', $csv);
        $this->assertStringContainsString('Kelas Pertama', $csv);
        $this->assertStringContainsString('Kelas Kedua', $csv);
    }

    public function test_all_enrollment_export_is_available_to_admins_only_and_not_linked_from_dashboard(): void
    {
        $this->get(route('admin.enrollments.export-all'))->assertRedirect(route('login'));

        $member = User::factory()->create(['is_admin' => false]);
        $this->actingAs($member)
            ->get(route('admin.enrollments.export-all'))
            ->assertForbidden();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Ekspor semua pendaftar')
            ->assertDontSee(route('admin.enrollments.export-all'));
    }

    public function test_enrollment_export_is_available_to_admins_only(): void
    {
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $course = $this->course($category);

        $this->get(route('admin.enrollments.export', $course))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.enrollments.export', $course))
            ->assertForbidden();
    }

    public function test_course_management_page_shows_a_direct_csv_export_link_for_each_course(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);
        $course = $this->course($category, 'Kelas CSV');

        $this->actingAs($admin)
            ->get(route('admin.courses.index'))
            ->assertOk()
            ->assertSee('↓ CSV')
            ->assertSee(route('admin.enrollments.export', $course));
    }

    private function course(Category $category, string $title = 'Kelas Digital'): Course
    {
        return Course::create([
            'category_id' => $category->id,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'description' => 'Deskripsi kelas.',
            'instructor' => 'Instruktur',
            'price' => 0,
            'status' => CourseStatus::Published,
        ]);
    }
}
