@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">اتوماسیون‌های سازمانی</h1>
            <p class="mt-1 text-sm text-gray-500">قانون تعریف کنید تا سیستم بر اساس رویدادها خودکار اقدام کند</p>
        </div>
        <a href="{{ route('management.index') }}" class="text-sm text-blue-600 hover:underline">بازگشت</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="lg:col-span-1">
            <div class="rounded-2xl bg-white p-5 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">تعریف اتوماسیون جدید</h2>
                <form action="{{ route('management.automations.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">نام قانون</label>
                        <input type="text" name="name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">رویداد</label>
                        <select name="event" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach(\App\Models\AutomationRule::EVENTS as $event)
                                <option value="{{ $event }}">{{ \App\Models\AutomationRule::eventLabel($event) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">دپارتمان</label>
                            <select name="department_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">همه</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">اولویت</label>
                            <select name="priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">همه</option>
                                @foreach(\App\Models\Task::PRIORITIES as $priority)
                                    <option value="{{ $priority }}">{{ \App\Models\Task::priorityLabel($priority) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">اقدام</label>
                        <select name="action" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach(\App\Models\AutomationRule::ACTIONS as $action)
                                <option value="{{ $action }}">{{ \App\Models\AutomationRule::actionLabel($action) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">کاربر هدف</label>
                            <select name="target_user_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">انتخاب نشده</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">نقش هدف</label>
                            <select name="target_role" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">انتخاب نشده</option>
                                <option value="admin">ادمین</option>
                                <option value="manager">مدیر</option>
                                <option value="employee">کارمند</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">عنوان وظیفه خودکار</label>
                        <input type="text" name="task_title_template" placeholder="مثلاً پیگیری نامه {letter_reference}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">شرح وظیفه خودکار</label>
                        <textarea name="task_description_template" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">اولویت وظیفه</label>
                            <select name="task_priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">پیش‌فرض</option>
                                @foreach(\App\Models\Task::PRIORITIES as $priority)
                                    <option value="{{ $priority }}">{{ \App\Models\Task::priorityLabel($priority) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">مهلت (روز)</label>
                            <input type="number" min="1" max="30" name="due_in_days" value="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600">
                        فعال
                    </label>
                    <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        ثبت اتوماسیون
                    </button>
                </form>
            </div>
        </section>

        <section class="space-y-4 lg:col-span-2">
            @forelse($rules as $rule)
                <div class="rounded-2xl bg-white p-5 shadow">
                    <form action="{{ route('management.automations.update', $rule) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">نام</label>
                                <input type="text" name="name" value="{{ $rule->name }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">رویداد</label>
                                <select name="event" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    @foreach(\App\Models\AutomationRule::EVENTS as $event)
                                        <option value="{{ $event }}" @selected($rule->event === $event)>{{ \App\Models\AutomationRule::eventLabel($event) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">اقدام</label>
                                <select name="action" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    @foreach(\App\Models\AutomationRule::ACTIONS as $action)
                                        <option value="{{ $action }}" @selected($rule->action === $action)>{{ \App\Models\AutomationRule::actionLabel($action) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">دپارتمان</label>
                                <select name="department_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">همه</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected($rule->department_id == $department->id)>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">اولویت</label>
                                <select name="priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">همه</option>
                                    @foreach(\App\Models\Task::PRIORITIES as $priority)
                                        <option value="{{ $priority }}" @selected($rule->priority === $priority)>{{ \App\Models\Task::priorityLabel($priority) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">کاربر هدف</label>
                                <select name="target_user_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">انتخاب نشده</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected($rule->target_user_id == $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">نقش هدف</label>
                                <select name="target_role" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">انتخاب نشده</option>
                                    <option value="admin" @selected($rule->target_role === 'admin')>ادمین</option>
                                    <option value="manager" @selected($rule->target_role === 'manager')>مدیر</option>
                                    <option value="employee" @selected($rule->target_role === 'employee')>کارمند</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">اولویت وظیفه</label>
                                <select name="task_priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">پیش‌فرض</option>
                                    @foreach(\App\Models\Task::PRIORITIES as $priority)
                                        <option value="{{ $priority }}" @selected($rule->task_priority === $priority)>{{ \App\Models\Task::priorityLabel($priority) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">مهلت (روز)</label>
                                <input type="number" min="1" max="30" name="due_in_days" value="{{ $rule->due_in_days }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700">عنوان وظیفه</label>
                                <input type="text" name="task_title_template" value="{{ $rule->task_title_template }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700">شرح وظیفه</label>
                                <textarea name="task_description_template" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ $rule->task_description_template }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="is_active" value="1" @checked($rule->is_active) class="rounded border-gray-300 text-blue-600">
                                فعال
                            </label>
                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">ذخیره</button>
                        </div>
                    </form>

                    <form action="{{ route('management.automations.destroy', $rule) }}" method="POST" class="mt-3 text-left">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700">حذف</button>
                    </form>
                </div>
            @empty
                <div class="rounded-2xl bg-white p-6 text-sm text-gray-500 shadow">هنوز اتوماسیونی ثبت نشده است.</div>
            @endforelse
        </section>
    </div>
</div>
@endsection
