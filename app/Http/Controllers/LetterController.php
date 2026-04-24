<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Letter;
use App\Models\LetterApproval;
use App\Models\LetterReferral;
use App\Models\User;
use App\Notifications\LetterCreatedNotification;
use App\Notifications\LetterApprovalDecisionNotification;
use App\Notifications\LetterApprovalRequestedNotification;
use App\Support\AutomationEngine;
use App\Support\CollaborationService;
use App\Support\LetterReferenceNumberGenerator;
use App\Support\RecordTimelineBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class LetterController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Letter::query()->with(['user', 'department']);

        $query->when(request('status'), function ($q, $status) {
            $q->where('status', $status);
        });

        $query->when(request('priority'), function ($q, $priority) {
            $q->where('priority', $priority);
        });

        $query->when(request('type'), function ($q, $type) {
            $q->where('type', $type);
        });

        $query->when(request('department_id'), function ($q, $departmentId) {
            $q->where('department_id', $departmentId);
        });

        $query->when(request('from'), function ($q, $from) {
            $q->whereDate('created_at', '>=', $from);
        });

        $query->when(request('to'), function ($q, $to) {
            $q->whereDate('created_at', '<=', $to);
        });

        $query->when(request('overdue'), function ($q) {
            $q->whereNotNull('due_date')
                ->whereDate('due_date', '<', now())
                ->whereNotIn('status', [Letter::STATUS_COMPLETED, Letter::STATUS_ARCHIVED]);
        });

        $query->when(request('q'), function ($q, $search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('subject', 'like', '%' . $search . '%')
                    ->orWhere('reference_number', 'like', '%' . $search . '%');
            });
        });

        $this->scopeLettersForUser($query, $user);

        $letters = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        $statuses = [Letter::STATUS_PENDING, Letter::STATUS_IN_PROGRESS, Letter::STATUS_COMPLETED, Letter::STATUS_ARCHIVED];
        $priorities = Letter::PRIORITIES;
        $types = Letter::TYPES;

        return view('letters.list', compact('letters', 'departments', 'statuses', 'priorities', 'types'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_LETTERS_CREATE), 403);

        $user = auth()->user();
        $canPickDepartment = $user->isAdmin();
        $departments = Department::query()->latest()->get(['id', 'name']);
        $userDepartment = $user->department;

        if (! $canPickDepartment && $userDepartment) {
            $departments = Department::query()->where('id', $userDepartment->id)->get(['id', 'name']);
        }

        return view('letters.create', compact('departments', 'canPickDepartment', 'userDepartment'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(User::PERMISSION_LETTERS_CREATE), 403);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'type' => ['required', 'in:' . implode(',', Letter::TYPES)],
            'priority' => ['required', 'in:' . implode(',', Letter::PRIORITIES)],
            'content' => ['required', 'string'],
            'due_date' => ['nullable', 'date'],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ]);

        if (! $user->isAdmin() && $user->department_id) {
            $validated['department_id'] = $user->department_id;
        }

        $payload = [];
        if (Schema::hasColumn('letters', 'title')) {
            $payload['title'] = $validated['subject'];
        }
        if (Schema::hasColumn('letters', 'subject')) {
            $payload['subject'] = $validated['subject'];
        }
        if (Schema::hasColumn('letters', 'content')) {
            $payload['content'] = $validated['content'];
        }
        if (Schema::hasColumn('letters', 'body')) {
            $payload['body'] = $validated['content'];
        }
        if (Schema::hasColumn('letters', 'from_user_id')) {
            $payload['from_user_id'] = $user->id;
        }
        if (Schema::hasColumn('letters', 'user_id')) {
            $payload['user_id'] = $user->id;
        }

        $payload['department_id'] = $validated['department_id'];
        $payload['type'] = $validated['type'];
        $payload['priority'] = $validated['priority'];
        $payload['reference_number'] = LetterReferenceNumberGenerator::generate($validated['type']);

        if (Schema::hasColumn('letters', 'due_date')) {
            $payload['due_date'] = $validated['due_date'] ?? null;
        }
        if (Schema::hasColumn('letters', 'status')) {
            $payload['status'] = Letter::STATUS_PENDING;
        }

        $letter = Letter::query()->create($payload);

        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store('letter-attachments', 'public');

            $letter->attachments()->create([
                'user_id' => $user->id,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        if ($letter->department_id) {
            $department = Department::query()->with('users')->find($letter->department_id);
            if ($department) {
                $department->users
                    ->where('id', '!=', $user->id)
                    ->each(function ($departmentUser) use ($letter) {
                        $departmentUser->notify(new LetterCreatedNotification($letter));
                    });
            }
        }

        app(AutomationEngine::class)->trigger('letter_created', [
            'letter' => $letter,
            'letter_id' => $letter->id,
            'letter_subject' => $letter->subject,
            'letter_reference' => $letter->reference_number,
            'department_id' => $letter->department_id,
            'department_name' => $letter->department?->name,
            'priority' => $letter->priority,
            'actor_id' => $user->id,
        ]);

        return redirect()->route('letters.view', $letter)->with('success', 'نامه با موفقیت ایجاد شد.');
    }

    public function show(Letter $letter)
    {
        $user = auth()->user();
        abort_unless($this->canViewLetter($letter, $user), 403);

        $letter->load([
            'user',
            'department',
            'attachments',
            'tasks',
            'referrals.fromUser:id,name',
            'referrals.toUser:id,name',
            'comments.user:id,name',
            'auditLogs.user:id,name',
            'followers.user:id,name,email',
            'approvals.requestedBy:id,name',
            'approvals.approver:id,name',
        ]);

        $canManageStatus = $user->hasPermission(User::PERMISSION_LETTERS_MANAGE_STATUS);
        $canRefer = $user->hasPermission(User::PERMISSION_LETTERS_REFER);
        $referrableUsers = $canRefer
            ? User::query()->where('id', '!=', $user->id)->orderBy('name')->get(['id', 'name', 'department_id'])
            : collect();

        $activeReferral = $letter->referrals
            ->where('to_user_id', $user->id)
            ->sortByDesc(fn (LetterReferral $referral) => $referral->referred_at ?? $referral->created_at)
            ->first(fn (LetterReferral $referral) => in_array($referral->status, [
                LetterReferral::STATUS_PENDING,
                LetterReferral::STATUS_ACCEPTED,
            ], true));

        $timeline = RecordTimelineBuilder::forLetter($letter);
        $isFollowing = $letter->isFollowedBy($user);
        $canRequestApproval = $user->hasPermission(User::PERMISSION_LETTERS_REQUEST_APPROVAL);
        $approvalCandidates = $canRequestApproval ? $this->approvalCandidatesFor($user, $letter) : collect();
        $pendingApprovalForUser = $letter->approvals
            ->first(fn (LetterApproval $approval) => $approval->status === LetterApproval::STATUS_PENDING && (int) $approval->approver_id === (int) $user->id);

        return view('letters.view', compact(
            'letter',
            'canManageStatus',
            'canRefer',
            'referrableUsers',
            'activeReferral',
            'timeline',
            'isFollowing',
            'canRequestApproval',
            'approvalCandidates',
            'pendingApprovalForUser'
        ));
    }

    public function updateStatus(Request $request, Letter $letter): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(User::PERMISSION_LETTERS_MANAGE_STATUS), 403);
        abort_unless($this->canViewLetter($letter, $user), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,archived'],
        ]);

        $letter->update([
            'status' => $validated['status'],
        ]);

        if ($letter->wasChanged('status')) {
            app(CollaborationService::class)->notifyFollowers(
                $letter,
                'تغییر وضعیت نامه',
                sprintf(
                    '%s وضعیت نامه «%s» را به %s تغییر داد.',
                    $user->name,
                    $letter->subject ?? '—',
                    Letter::statusLabel($letter->status)
                ),
                [$user->id]
            );
        }

        return redirect()->route('letters.view', $letter)->with('success', 'وضعیت نامه بروزرسانی شد.');
    }

    public function refer(Request $request, Letter $letter)
    {
        $user = auth()->user();
        abort_unless($this->canViewLetter($letter, $user), 403);
        abort_unless($user->hasPermission(User::PERMISSION_LETTERS_REFER), 403);

        $validated = $request->validate([
            'to_user_id' => ['required', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:1500'],
        ]);

        if ((int) $validated['to_user_id'] === (int) $user->id) {
            return back()->withErrors(['to_user_id' => 'ارجاع به خودتان مجاز نیست.'])->withInput();
        }

        $hasOpenReferral = $letter->referrals()
            ->where('to_user_id', $validated['to_user_id'])
            ->whereIn('status', [LetterReferral::STATUS_PENDING, LetterReferral::STATUS_ACCEPTED])
            ->exists();

        if ($hasOpenReferral) {
            return back()->withErrors(['to_user_id' => 'برای این کاربر یک ارجاع باز ثبت شده است.'])->withInput();
        }

        $letter->referrals()->create([
            'from_user_id' => $user->id,
            'to_user_id' => $validated['to_user_id'],
            'assigned_by_user_id' => $user->id,
            'status' => LetterReferral::STATUS_PENDING,
            'note' => $validated['note'] ?? null,
            'referred_at' => now(),
        ]);

        if ($letter->status !== Letter::STATUS_ARCHIVED) {
            $letter->update(['status' => Letter::STATUS_IN_PROGRESS]);
        }

        app(CollaborationService::class)->notifyFollowers(
            $letter,
            'ارجاع جدید روی نامه',
            sprintf(
                '%s نامه «%s» را به %s ارجاع داد.',
                $user->name,
                $letter->subject ?? '—',
                User::query()->find($validated['to_user_id'])?->name ?? 'کاربر'
            ),
            [$user->id, (int) $validated['to_user_id']]
        );

        return redirect()->route('letters.view', $letter)->with('success', 'نامه با موفقیت ارجاع شد.');
    }

    public function updateReferral(Request $request, Letter $letter, LetterReferral $referral)
    {
        abort_unless($referral->letter_id === $letter->id, 404);

        $user = auth()->user();
        abort_unless(
            $referral->to_user_id === $user->id || $user->hasPermission(User::PERMISSION_LETTERS_MANAGE_STATUS),
            403
        );

        $validated = $request->validate([
            'status' => ['required', 'in:accepted,completed,rejected'],
            'response_note' => ['nullable', 'string', 'max:1500'],
        ]);

        $referral->update([
            'status' => $validated['status'],
            'response_note' => $validated['response_note'] ?? null,
            'responded_at' => now(),
        ]);

        $this->syncLetterStatusFromReferrals($letter->fresh('referrals'));

        app(CollaborationService::class)->notifyFollowers(
            $letter,
            'پاسخ ارجاع نامه',
            sprintf(
                '%s روی ارجاع نامه «%s» وضعیت %s را ثبت کرد.',
                $user->name,
                $letter->subject ?? '—',
                LetterReferral::statusLabel($referral->status)
            ),
            [$user->id]
        );

        return redirect()->route('letters.view', $letter)->with('success', 'پاسخ ارجاع ثبت شد.');
    }

    public function storeComment(Request $request, Letter $letter): RedirectResponse
    {
        abort_unless($this->canViewLetter($letter, auth()->user()), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment = $letter->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        app(CollaborationService::class)->handleComment($comment->load('user', 'commentable'));

        return redirect()->route('letters.view', $letter)->with('success', 'یادداشت ثبت شد.');
    }

    public function follow(Letter $letter): RedirectResponse
    {
        abort_unless($this->canViewLetter($letter, auth()->user()), 403);

        $letter->followers()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('letters.view', $letter)->with('success', 'نامه به موارد دنبال‌شده شما اضافه شد.');
    }

    public function unfollow(Letter $letter): RedirectResponse
    {
        abort_unless($this->canViewLetter($letter, auth()->user()), 403);

        $letter->followers()->where('user_id', auth()->id())->delete();

        return redirect()->route('letters.view', $letter)->with('success', 'نامه از موارد دنبال‌شده حذف شد.');
    }

    public function requestApproval(Request $request, Letter $letter): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($this->canViewLetter($letter, $user), 403);
        abort_unless($user->hasPermission(User::PERMISSION_LETTERS_REQUEST_APPROVAL), 403);

        if ($letter->approvals()->where('status', LetterApproval::STATUS_PENDING)->exists()) {
            return back()->withErrors(['approver_id' => 'برای این نامه یک درخواست تایید باز وجود دارد.'])->withInput();
        }

        $validated = $request->validate([
            'approver_id' => ['required', 'exists:users,id'],
            'request_note' => ['nullable', 'string', 'max:1500'],
        ]);

        $allowedApproverIds = $this->approvalCandidatesFor($user, $letter)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (! in_array((int) $validated['approver_id'], $allowedApproverIds, true)) {
            return back()->withErrors(['approver_id' => 'این کاربر برای تایید این نامه مجاز نیست.'])->withInput();
        }

        $approval = $letter->approvals()->create([
            'requested_by_user_id' => $user->id,
            'approver_id' => $validated['approver_id'],
            'status' => LetterApproval::STATUS_PENDING,
            'request_note' => $validated['request_note'] ?? null,
            'requested_at' => now(),
        ]);

        $approval->loadMissing('letter', 'requestedBy', 'approver');
        $approval->approver?->notify(new LetterApprovalRequestedNotification($approval));

        app(CollaborationService::class)->notifyFollowers(
            $letter,
            'درخواست تایید نامه',
            sprintf(
                '%s برای نامه «%s» درخواست تایید ثبت کرد.',
                $user->name,
                $letter->subject ?? '—'
            ),
            [$user->id, (int) $validated['approver_id']]
        );

        return redirect()->route('letters.view', $letter)->with('success', 'درخواست تایید ثبت شد.');
    }

    public function decideApproval(Request $request, Letter $letter, LetterApproval $approval): RedirectResponse
    {
        abort_unless((int) $approval->letter_id === (int) $letter->id, 404);

        $user = auth()->user();
        abort_unless(
            ((int) $approval->approver_id === (int) $user->id || $user->hasPermission(User::PERMISSION_LETTERS_APPROVE))
            && $this->canViewLetter($letter, $user),
            403
        );

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'decision_note' => ['nullable', 'string', 'max:1500'],
        ]);

        $approval->update([
            'status' => $validated['status'],
            'decision_note' => $validated['decision_note'] ?? null,
            'decided_at' => now(),
        ]);

        $approval->loadMissing('letter', 'requestedBy', 'approver');
        $approval->requestedBy?->notify(new LetterApprovalDecisionNotification($approval));

        app(CollaborationService::class)->notifyFollowers(
            $letter,
            'نتیجه تایید نامه',
            sprintf(
                '%s درخواست تایید نامه «%s» را %s.',
                $user->name,
                $letter->subject ?? '—',
                $approval->status === LetterApproval::STATUS_APPROVED ? 'تایید کرد' : 'رد کرد'
            ),
            [$user->id, (int) $approval->requested_by_user_id]
        );

        return redirect()->route('letters.view', $letter)->with('success', 'نتیجه تایید ثبت شد.');
    }

    private function scopeLettersForUser(Builder $query, User $user): void
    {
        if ($user->hasPermission(User::PERMISSION_LETTERS_VIEW_ALL)) {
            return;
        }

        if ($user->hasPermission(User::PERMISSION_LETTERS_VIEW_DEPARTMENT) && $user->department_id) {
            $query->where('department_id', $user->department_id);

            return;
        }

        $query->where(function (Builder $sub) use ($user) {
            $sub->where('user_id', $user->id)
                ->orWhereHas('referrals', function (Builder $referrals) use ($user) {
                    $referrals->where('to_user_id', $user->id)
                        ->orWhere('from_user_id', $user->id)
                        ->orWhere('assigned_by_user_id', $user->id);
                });
        });
    }

    private function canViewLetter(Letter $letter, User $user): bool
    {
        if ($user->hasPermission(User::PERMISSION_LETTERS_VIEW_ALL)) {
            return true;
        }

        if ($user->hasPermission(User::PERMISSION_LETTERS_VIEW_DEPARTMENT) && $user->department_id) {
            return (int) $letter->department_id === (int) $user->department_id;
        }

        if ((int) $letter->user_id === (int) $user->id) {
            return true;
        }

        return $letter->referrals()
            ->where(function (Builder $query) use ($user) {
                $query->where('to_user_id', $user->id)
                    ->orWhere('from_user_id', $user->id)
                    ->orWhere('assigned_by_user_id', $user->id);
            })
            ->exists();
    }

    private function syncLetterStatusFromReferrals(Letter $letter): void
    {
        if ($letter->status === Letter::STATUS_ARCHIVED) {
            return;
        }

        $statuses = $letter->referrals->pluck('status');
        if ($statuses->isEmpty()) {
            return;
        }

        if ($statuses->contains(fn ($status) => in_array($status, [LetterReferral::STATUS_PENDING, LetterReferral::STATUS_ACCEPTED], true))) {
            $newStatus = Letter::STATUS_IN_PROGRESS;
        } elseif ($statuses->contains(LetterReferral::STATUS_COMPLETED)) {
            $newStatus = Letter::STATUS_COMPLETED;
        } elseif ($statuses->every(fn ($status) => $status === LetterReferral::STATUS_REJECTED)) {
            $newStatus = Letter::STATUS_PENDING;
        } else {
            return;
        }

        if ($letter->status !== $newStatus) {
            $letter->update(['status' => $newStatus]);
        }
    }

    private function approvalCandidatesFor(User $user, Letter $letter): Collection
    {
        return User::query()
            ->where('id', '!=', $user->id)
            ->where(function (Builder $query) use ($letter) {
                $query->where('role', User::ROLE_ADMIN)
                    ->orWhere(function (Builder $subQuery) use ($letter) {
                        $subQuery->where('role', User::ROLE_MANAGER);

                        if ($letter->department_id) {
                            $subQuery->where('department_id', $letter->department_id);
                        }
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
