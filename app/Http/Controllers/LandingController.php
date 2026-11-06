<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Mail\EnrollmentConfirmation;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(Request $request): View
    {
        $searchInput = $request->query('search');
        $categoryInput = $request->query('category');
        $search = is_string($searchInput) ? trim($searchInput) : '';
        $categorySlug = is_string($categoryInput) ? $categoryInput : '';

        $courses = Course::query()
            ->with('category')
            ->withCount('enrollments')
            ->where('status', CourseStatus::Published)
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('instructor', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            }))
            ->when($categorySlug, fn ($query) => $query->whereHas(
                'category',
                fn ($query) => $query->where('slug', $categorySlug),
            ))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('landing.index', [
            'courses' => $courses,
            'categories' => Category::query()->orderBy('name')->get(),
            'search' => $search,
            'selectedCategory' => $categorySlug,
        ]);
    }

    public function show(Course $course): View
    {
        abort_unless($course->status === CourseStatus::Published, 404);

        return view('landing.show', [
            'course' => $course->load('category'),
        ]);
    }

    public function enroll(StoreEnrollmentRequest $request, Course $course): RedirectResponse
    {
        abort_unless($course->status === CourseStatus::Published, 404);

        $enrollment = $course->enrollments()->create($request->validated());

        Mail::to($enrollment->email)->queue(
            new EnrollmentConfirmation($enrollment->name, $course->title),
        );

        return back()->with('success', 'Pendaftaran berhasil. Email konfirmasi telah dijadwalkan untuk dikirim ke alamat email Anda.');
    }
}
