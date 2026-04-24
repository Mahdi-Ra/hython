<?php

namespace App\Support;

use App\Models\Comment;
use App\Models\Letter;
use App\Models\Task;
use App\Models\User;
use App\Notifications\FollowedRecordUpdatedNotification;
use App\Notifications\MentionedInCommentNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CollaborationService
{
    public function handleComment(Comment $comment): void
    {
        $comment->loadMissing('user', 'commentable');

        $record = $comment->commentable;
        if (! $record instanceof Model) {
            return;
        }

        $mentionedUsers = $this->extractMentionedUsers($comment->body);

        $mentionedUsers
            ->reject(fn (User $user) => (int) $user->id === (int) $comment->user_id)
            ->each(fn (User $user) => $user->notify(new MentionedInCommentNotification($comment, $record)));

        $this->notifyFollowers(
            $record,
            'فعالیت جدید روی مورد دنبال‌شده',
            sprintf(
                '%s روی %s «%s» یادداشت جدید ثبت کرد.',
                $comment->user?->name ?? 'کاربر',
                $this->recordLabel($record),
                $this->recordTitle($record)
            ),
            array_merge([$comment->user_id], $mentionedUsers->pluck('id')->all())
        );
    }

    public function notifyFollowers(Model $record, string $title, string $body, array $excludedUserIds = []): void
    {
        if (! method_exists($record, 'followers')) {
            return;
        }

        $record->loadMissing('followers.user');

        $record->followers
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->reject(fn (User $user) => in_array((int) $user->id, array_map('intval', $excludedUserIds), true))
            ->each(fn (User $user) => $user->notify(new FollowedRecordUpdatedNotification($record, $title, $body)));
    }

    public function extractMentionedUsers(?string $body): Collection
    {
        if (! filled($body)) {
            return collect();
        }

        preg_match_all('/(^|\\s)@([A-Z0-9._%+\\-]+@[A-Z0-9.\\-]+\\.[A-Z]{2,})/iu', $body, $matches);
        $emails = collect($matches[2] ?? [])->map(fn (string $email) => mb_strtolower($email))->unique()->values();

        if ($emails->isEmpty()) {
            return collect();
        }

        return User::query()->whereIn('email', $emails)->get();
    }

    public function recordLabel(object $record): string
    {
        return $record instanceof Task ? 'وظیفه' : 'نامه';
    }

    public function recordTitle(object $record): string
    {
        if ($record instanceof Task) {
            return $record->title;
        }

        return $record instanceof Letter ? ($record->subject ?? '—') : '—';
    }
}
