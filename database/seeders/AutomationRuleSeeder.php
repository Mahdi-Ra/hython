<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class AutomationRuleSeeder extends Seeder
{
    public function run(): void
    {
        AutomationRule::query()->updateOrCreate(
            ['name' => 'ایجاد وظیفه برای مدیر دپارتمان بعد از ثبت نامه'],
            [
                'event' => AutomationRule::EVENT_LETTER_CREATED,
                'action' => AutomationRule::ACTION_CREATE_TASK,
                'target_role' => User::ROLE_MANAGER,
                'target_user_id' => null,
                'department_id' => null,
                'priority' => null,
                'task_title_template' => 'بررسی نامه {letter_reference}',
                'task_description_template' => 'یک نامه جدید با موضوع «{letter_subject}» ثبت شده و نیاز به بررسی مدیر دپارتمان دارد.',
                'task_priority' => Task::PRIORITY_HIGH,
                'due_in_days' => 1,
                'is_active' => true,
            ]
        );

        AutomationRule::query()->updateOrCreate(
            ['name' => 'اعلان به مدیر دپارتمان هنگام معوق شدن وظیفه'],
            [
                'event' => AutomationRule::EVENT_TASK_OVERDUE,
                'action' => AutomationRule::ACTION_NOTIFY_ROLE,
                'target_role' => User::ROLE_MANAGER,
                'target_user_id' => null,
                'department_id' => null,
                'priority' => null,
                'task_title_template' => null,
                'task_description_template' => null,
                'task_priority' => null,
                'due_in_days' => null,
                'is_active' => true,
            ]
        );

        AutomationRule::query()->updateOrCreate(
            ['name' => 'اعلان به مدیر دپارتمان هنگام معوق شدن نامه'],
            [
                'event' => AutomationRule::EVENT_LETTER_OVERDUE,
                'action' => AutomationRule::ACTION_NOTIFY_ROLE,
                'target_role' => User::ROLE_MANAGER,
                'target_user_id' => null,
                'department_id' => null,
                'priority' => null,
                'task_title_template' => null,
                'task_description_template' => null,
                'task_priority' => null,
                'due_in_days' => null,
                'is_active' => true,
            ]
        );
    }
}
