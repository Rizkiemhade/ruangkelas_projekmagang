<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'courseCount' => Course::count(),
            'enrollmentCount' => Enrollment::count(),
            'pendingEnrollmentCount' => Enrollment::query()
                ->where('status', EnrollmentStatus::Pending)
                ->count(),
            'categoryCount' => Category::count(),
            'latestEnrollments' => Enrollment::query()->with('course')->latest()->take(5)->get(),
        ]);
    }
}
