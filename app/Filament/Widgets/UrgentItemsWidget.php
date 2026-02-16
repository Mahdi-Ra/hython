<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\LetterResource;
use App\Filament\Resources\TaskResource;
use App\Models\Letter;
use App\Models\Task;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class UrgentItemsWidget extends Widget
{
    protected static ?string $heading = 'موارد فوری';

    protected static ?string $description = 'نامه‌ها و وظایف فوری یا با اولویت بالا';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    protected static string $view = 'filament.widgets.urgent-items-widget';

    public function getViewData(): array
    {
        $userId = auth()->id();

        $urgentLetters = Letter::query()
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('referrals', fn ($q) => $q->where('to_user_id', $userId));
            })
            ->whereIn('priority', [Letter::PRIORITY_URGENT, Letter::PRIORITY_HIGH])
            ->whereNotIn('status', [Letter::STATUS_COMPLETED, Letter::STATUS_ARCHIVED])
            ->with('user')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $urgentTasks = Task::query()
            ->where('assigned_to_user_id', $userId)
            ->whereIn('priority', [Task::PRIORITY_URGENT, Task::PRIORITY_HIGH])
            ->whereNull('completed_at')
            ->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])
            ->with('letter')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return [
            'urgentLetters' => $urgentLetters,
            'urgentTasks' => $urgentTasks,
        ];
    }
}
