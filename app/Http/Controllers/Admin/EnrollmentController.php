<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentController extends Controller
{
    public function all(Request $request): View
    {
        $searchInput = $request->query('search');
        $search = is_string($searchInput) ? trim($searchInput) : '';

        return view('admin.enrollments.all', [
            'enrollments' => Enrollment::query()
                ->with('course:id,title,slug')
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('course', fn ($query) => $query->where('title', 'like', "%{$search}%"));
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function index(Request $request, Course $course): View
    {
        $searchInput = $request->query('search');
        $search = is_string($searchInput) ? trim($searchInput) : '';

        return view('admin.enrollments.index', [
            'course' => $course,
            'enrollments' => $course->enrollments()
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'statuses' => EnrollmentStatus::cases(),
            'search' => $search,
        ]);
    }

    public function exportAll(): StreamedResponse
    {
        return response()->streamDownload(static function (): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                throw new RuntimeException('File CSV pendaftar gagal dibuka untuk ditulis.');
            }

            if (fwrite($output, "\xEF\xBB\xBF") === false) {
                throw new RuntimeException('Tanda encoding UTF-8 gagal ditulis ke file CSV.');
            }

            self::writeCsvRow($output, ['ID', 'Nama', 'Email', 'Nomor telepon', 'Kelas', 'Status', 'Tanggal daftar']);

            foreach (Enrollment::query()
                ->with('course:id,title')
                ->orderBy('id')
                ->lazy(500) as $enrollment) {
                self::writeCsvRow($output, [
                    $enrollment->id,
                    $enrollment->name,
                    $enrollment->email,
                    $enrollment->phone,
                    $enrollment->course->title,
                    $enrollment->status->label(),
                    $enrollment->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            if (! fclose($output)) {
                throw new RuntimeException('File CSV pendaftar gagal ditutup.');
            }
        }, 'pendaftar-semua-kelas.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function export(Course $course): StreamedResponse
    {
        return response()->streamDownload(static function () use ($course): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                throw new RuntimeException('File CSV pendaftar gagal dibuka untuk ditulis.');
            }

            if (fwrite($output, "\xEF\xBB\xBF") === false) {
                throw new RuntimeException('Tanda encoding UTF-8 gagal ditulis ke file CSV.');
            }

            self::writeCsvRow($output, ['ID', 'Nama', 'Email', 'Nomor telepon', 'Status', 'Tanggal daftar']);

            foreach ($course->enrollments()->orderBy('id')->cursor() as $enrollment) {
                self::writeCsvRow($output, [
                    $enrollment->id,
                    $enrollment->name,
                    $enrollment->email,
                    $enrollment->phone,
                    $enrollment->status->label(),
                    $enrollment->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            if (! fclose($output)) {
                throw new RuntimeException('File CSV pendaftar gagal ditutup.');
            }
        }, 'pendaftar-'.Str::slug($course->title).'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function update(Request $request, Course $course, Enrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->course_id === $course->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::enum(EnrollmentStatus::class)],
        ], [
            'status.required' => 'Status pendaftaran wajib dipilih.',
            'status.enum' => 'Status pendaftaran tidak valid.',
        ]);

        $enrollment->update($data);

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    public function destroy(Course $course, Enrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->course_id === $course->id, 404);

        $enrollment->delete();

        return back()->with('success', 'Data peserta berhasil dihapus.');
    }

    /**
     * Prefix spreadsheet formula markers so exported user input remains plain text.
     *
     * @param  resource  $output
     * @param  array<int, string|int>  $values
     */
    private static function writeCsvRow($output, array $values): void
    {
        $safeValues = array_map(static function (string|int $value): string {
            $value = (string) $value;

            return preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $value) === 1
                ? "'".$value
                : $value;
        }, $values);

        if (fputcsv($output, $safeValues, ',', '"', '') === false) {
            throw new RuntimeException('Baris data pendaftar gagal ditulis ke file CSV.');
        }
    }
}
