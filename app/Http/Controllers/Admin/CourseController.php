<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $searchInput = $request->query('search');
        $search = is_string($searchInput) ? trim($searchInput) : '';

        return view('admin.courses.index', [
            'courses' => Course::query()
                ->with('category')
                ->withCount('enrollments')
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('instructor', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                }))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.courses.form', [
            'course' => new Course,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(5));

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request);
        }

        Course::create($data);

        return redirect()->route('admin.courses.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.form', [
            'course' => $course,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(StoreCourseRequest $request, Course $course): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $newImage = $this->storeImage($request);
            $data['image_path'] = $newImage;
            $oldImage = $course->image_path;
        } else {
            $oldImage = null;
        }

        $course->update($data);
        $this->deleteImage($oldImage);

        return redirect()->route('admin.courses.index')->with('success', 'Perubahan kelas berhasil disimpan.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $imagePath = $course->image_path;
        $course->delete();
        $this->deleteImage($imagePath);

        return redirect()->route('admin.courses.index')->with('success', 'Kelas dan data pendaftarannya berhasil dihapus.');
    }

    private function storeImage(StoreCourseRequest $request): string
    {
        $path = $request->file('image')->store('courses', 'public');

        if (! $path) {
            throw new RuntimeException('Gambar kelas gagal disimpan.');
        }

        return $path;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path) && ! Storage::disk('public')->delete($path)) {
            throw new RuntimeException('Gambar kelas gagal dihapus dari penyimpanan.');
        }
    }
}
