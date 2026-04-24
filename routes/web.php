<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\MyWorkController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkloadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AutomationRuleController as AdminAutomationRuleController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// گروه Routeهای محافظت‌شده با auth
Route::middleware('auth')->group(function () {

    // داشبورد
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/my-work', [MyWorkController::class, 'index'])->name('my-work.index');

    // نامه‌ها
    Route::get('/letters', [LetterController::class, 'index'])->name('letters.index');
    Route::get('/letters/create', [LetterController::class, 'create'])->name('letters.create');
    Route::post('/letters', [LetterController::class, 'store'])->name('letters.store');
    Route::get('/letters/{letter}', [LetterController::class, 'show'])->name('letters.view');
    Route::put('/letters/{letter}/status', [LetterController::class, 'updateStatus'])->name('letters.status');
    Route::post('/letters/{letter}/refer', [LetterController::class, 'refer'])->name('letters.refer');
    Route::put('/letters/{letter}/referrals/{referral}', [LetterController::class, 'updateReferral'])->name('letters.referrals.update');
    Route::post('/letters/{letter}/comments', [LetterController::class, 'storeComment'])->name('letters.comments.store');
    Route::post('/letters/{letter}/follow', [LetterController::class, 'follow'])->name('letters.follow');
    Route::delete('/letters/{letter}/follow', [LetterController::class, 'unfollow'])->name('letters.unfollow');
    Route::post('/letters/{letter}/approvals', [LetterController::class, 'requestApproval'])->name('letters.approvals.store');
    Route::put('/letters/{letter}/approvals/{approval}', [LetterController::class, 'decideApproval'])->name('letters.approvals.update');

    // وظایف
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/follow', [TaskController::class, 'follow'])->name('tasks.follow');
    Route::delete('/tasks/{task}/follow', [TaskController::class, 'unfollow'])->name('tasks.unfollow');

    // پیام‌ها (چت)
    Route::get('/messages', [ChatController::class, 'index'])->name('messages.index');
    Route::get('/messages/files/{message}/preview', [ChatController::class, 'preview'])->name('messages.preview');
    Route::get('/messages/files/{message}', [ChatController::class, 'download'])->name('messages.download');
    Route::get('/messages/{user}', [ChatController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [ChatController::class, 'store'])->name('messages.store');

    // اعلان‌ها
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // گزارش‌ها
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/kpis', [KpiController::class, 'index'])->name('kpis.index');
    Route::get('/workload', [WorkloadController::class, 'index'])->name('workload.index');

    // مدیریت سیستم (ادمین)
    Route::middleware('admin')->prefix('management')->name('management.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/departments', [AdminDepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [AdminDepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{department}/edit', [AdminDepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{department}', [AdminDepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [AdminDepartmentController::class, 'destroy'])->name('departments.destroy');

        Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
        Route::put('/roles/{user}', [AdminRoleController::class, 'update'])->name('roles.update');

        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit.index');
        Route::get('/automations', [AdminAutomationRuleController::class, 'index'])->name('automations.index');
        Route::post('/automations', [AdminAutomationRuleController::class, 'store'])->name('automations.store');
        Route::put('/automations/{automation}', [AdminAutomationRuleController::class, 'update'])->name('automations.update');
        Route::delete('/automations/{automation}', [AdminAutomationRuleController::class, 'destroy'])->name('automations.destroy');
    });

    // کارمندان
    Route::get('/employees', [UserController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [UserController::class, 'create'])->name('employees.create');
    Route::post('/employees', [UserController::class, 'store'])->name('employees.store');
    Route::get('/employees/{user}/edit', [UserController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{user}', [UserController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{user}', [UserController::class, 'destroy'])->name('employees.destroy');

    // پروفایل
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';
