<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\CourseTypeController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\TutorSalaryController;
use App\Http\Controllers\PupilController;
use App\Http\Controllers\ClassSessionController;
use App\Http\Controllers\TutorPresenceController;
use App\Http\Controllers\PupilPresenceController;
use App\Http\Controllers\UserController;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'role'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data
    Route::resource('grades', GradeController::class);
    Route::resource('course-types', CourseTypeController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('classes', SchoolClassController::class);

    // Tutor
    Route::resource('tutors', TutorController::class);
    Route::get('tutor-salaries', [TutorSalaryController::class, 'index'])->name('tutor-salaries.index');

    // Siswa
    Route::resource('pupils', PupilController::class);

    // Sesi Kelas & Presensi
    Route::resource('class-sessions', ClassSessionController::class)->only(['index', 'store', 'destroy']);

    // Admin: overview semua presensi tutor per minggu
    Route::get('tutor-presences', [TutorPresenceController::class, 'index'])->name('tutor-presences.index');

    // Tutor: self-service presensi
    Route::get('my-presences', [TutorPresenceController::class, 'myPresences'])->name('my-presences');
    Route::post('my-presences/sessions', [TutorPresenceController::class, 'storeMySession'])->name('my-presences.store');
    Route::put('my-presences/{presence}', [TutorPresenceController::class, 'updateMyPresence'])->name('my-presences.update');

    Route::prefix('class-sessions/{classSession}')->name('class-sessions.')->group(function () {
        Route::resource('pupil-presences', PupilPresenceController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

    // Manajemen User (admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
    });
});

require __DIR__ . '/auth.php';
