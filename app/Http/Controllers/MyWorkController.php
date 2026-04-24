<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterReferral;
use App\Models\Task;
use Illuminate\View\View;

class MyWorkController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $myTasks = Task::query()
            ->with('letter:id,uuid,subject,reference_number')
            ->forUser($user->id)
            ->where('status', '!=', Task::STATUS_COMPLETED)
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->take(8)
            ->get();
        $openTasksCount = Task::query()
            ->forUser($user->id)
            ->where('status', '!=', Task::STATUS_COMPLETED)
            ->count();

        $overdueTasks = Task::query()
            ->forUser($user->id)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('status', '!=', Task::STATUS_COMPLETED)
            ->count();

        $dueSoonTasks = Task::query()
            ->forUser($user->id)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(3)->toDateString()])
            ->where('status', '!=', Task::STATUS_COMPLETED)
            ->count();

        $myReferrals = LetterReferral::query()
            ->with(['letter.user:id,name', 'letter.department:id,name'])
            ->where('to_user_id', $user->id)
            ->whereIn('status', [LetterReferral::STATUS_PENDING, LetterReferral::STATUS_ACCEPTED])
            ->orderByRaw('CASE WHEN referred_at IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('referred_at')
            ->take(8)
            ->get();
        $activeReferralsCount = LetterReferral::query()
            ->where('to_user_id', $user->id)
            ->whereIn('status', [LetterReferral::STATUS_PENDING, LetterReferral::STATUS_ACCEPTED])
            ->count();

        $myLetters = Letter::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', [Letter::STATUS_COMPLETED, Letter::STATUS_ARCHIVED])
            ->latest()
            ->take(8)
            ->get();

        $upcomingItems = collect()
            ->merge($myTasks->map(fn ($task) => [
                'type' => 'task',
                'title' => $task->title,
                'due_date' => $task->due_date,
                'status' => Task::statusLabel($task->status),
                'url' => route('tasks.show', $task),
            ]))
            ->merge($myReferrals->map(fn ($referral) => [
                'type' => 'letter',
                'title' => $referral->letter?->subject ?? 'نامه',
                'due_date' => $referral->letter?->due_date,
                'status' => Letter::statusLabel($referral->letter?->status),
                'url' => $referral->letter ? route('letters.view', $referral->letter) : null,
            ]))
            ->filter(fn ($item) => filled($item['due_date']))
            ->sortBy('due_date')
            ->take(10)
            ->values();

        $latestNotifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get();

        return view('my-work.index', compact(
            'myTasks',
            'openTasksCount',
            'overdueTasks',
            'dueSoonTasks',
            'myReferrals',
            'activeReferralsCount',
            'myLetters',
            'upcomingItems',
            'latestNotifications'
        ));
    }
}
