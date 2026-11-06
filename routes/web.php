<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{article}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/kelas/{course}', [LandingController::class, 'show'])->name('courses.show');
Route::post('/kelas/{course}/daftar', [LandingController::class, 'enroll'])->name('enrollments.store');

Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
Route::post('/admin/login', [AuthController::class, 'store'])->middleware(['guest', 'throttle:5,1'])->name('login.store');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/profil', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::get('/artikel', [AdminArticleController::class, 'index'])->name('articles.index');
    Route::get('/artikel/tambah', [AdminArticleController::class, 'create'])->name('articles.create');
    Route::post('/artikel', [AdminArticleController::class, 'store'])->name('articles.store');
    Route::get('/artikel/{article}/ubah', [AdminArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/artikel/{article}', [AdminArticleController::class, 'update'])->name('articles.update');
    Route::delete('/artikel/{article}', [AdminArticleController::class, 'destroy'])->name('articles.destroy');

    Route::get('/kelas', [AdminCourseController::class, 'index'])->name('courses.index');
    Route::get('/kelas/tambah', [AdminCourseController::class, 'create'])->name('courses.create');
    Route::post('/kelas', [AdminCourseController::class, 'store'])->name('courses.store');
    Route::get('/kelas/{course}/ubah', [AdminCourseController::class, 'edit'])->name('courses.edit');
    Route::put('/kelas/{course}', [AdminCourseController::class, 'update'])->name('courses.update');
    Route::delete('/kelas/{course}', [AdminCourseController::class, 'destroy'])->name('courses.destroy');
    Route::get('/pendaftar', [AdminEnrollmentController::class, 'all'])->name('enrollments.all');
    Route::get('/kelas/{course}/pendaftar', [AdminEnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/kelas/{course}/pendaftar/export', [AdminEnrollmentController::class, 'export'])->name('enrollments.export');
    Route::patch('/kelas/{course}/pendaftar/{enrollment}', [AdminEnrollmentController::class, 'update'])->name('enrollments.update');
    Route::delete('/kelas/{course}/pendaftar/{enrollment}', [AdminEnrollmentController::class, 'destroy'])->name('enrollments.destroy');
    Route::get('/pendaftar/export', [AdminEnrollmentController::class, 'exportAll'])->name('enrollments.export-all');

    Route::get('/kategori', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::get('/kategori/tambah', [AdminCategoryController::class, 'create'])->name('categories.create');
    Route::post('/kategori', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::get('/kategori/{category}/ubah', [AdminCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/kategori/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/kategori/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
});
