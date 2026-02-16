# ساختار پروژه — سامانه مکاتبات و وظایف (MVP)

ساختار پوشه‌ها با تفکیک منطقی ماژول‌ها و آماده برای دمو و توسعه بعدی.

---

## درخت کامل پروژه

```
crm/
├── app/
│   ├── Filament/                          # پنل ادمین (RTL / فارسی)
│   │   ├── Resources/
│   │   │   ├── DepartmentResource.php
│   │   │   │   └── Pages/
│   │   │   │       ├── ListDepartments.php
│   │   │   │       ├── CreateDepartment.php
│   │   │   │       └── EditDepartment.php
│   │   │   ├── LetterResource.php
│   │   │   │   ├── Pages/
│   │   │   │   │   ├── ListLetters.php
│   │   │   │   │   ├── CreateLetter.php
│   │   │   │   │   ├── ViewLetter.php
│   │   │   │   │   └── EditLetter.php
│   │   │   │   └── RelationManagers/
│   │   │   │       ├── AttachmentsRelationManager.php
│   │   │   │       ├── ReferralsRelationManager.php
│   │   │   │       └── TasksRelationManager.php
│   │   │   ├── LetterReferralResource.php
│   │   │   │   └── Pages/
│   │   │   │       ├── ListLetterReferrals.php
│   │   │   │       ├── CreateLetterReferral.php
│   │   │   │       ├── ViewLetterReferral.php
│   │   │   │       └── EditLetterReferral.php
│   │   │   ├── TaskResource.php
│   │   │   │   └── Pages/
│   │   │   │       ├── ListTasks.php
│   │   │   │       ├── CreateTask.php
│   │   │   │       ├── ViewTask.php
│   │   │   │       └── EditTask.php
│   │   │   └── UserResource.php
│   │   │       └── Pages/
│   │   │           ├── ListUsers.php
│   │   │           ├── CreateUser.php
│   │   │           └── EditUser.php
│   │   └── Widgets/
│   │       ├── InboxLettersWidget.php
│   │       ├── PendingReferralsWidget.php
│   │       ├── MyTasksWidget.php
│   │       └── UrgentItemsWidget.php
│   │
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Controller.php
│   │       ├── AttachmentController.php
│   │       ├── NotificationController.php
│   │       └── Api/
│   │           ├── LetterController.php
│   │           └── TaskController.php
│   │
│   ├── Models/
│   │   ├── Concerns/
│   │   │   ├── Auditable.php              # تاریخچه و لاگ تغییرات
│   │   │   └── HasUuid.php
│   │   ├── AuditLog.php
│   │   ├── Attachment.php
│   │   ├── Comment.php
│   │   ├── Department.php
│   │   ├── Letter.php
│   │   ├── LetterReferral.php
│   │   ├── Task.php
│   │   └── User.php
│   │
│   ├── Notifications/
│   │   ├── LetterReferredNotification.php
│   │   └── TaskAssignedNotification.php
│   │
│   ├── Observers/
│   │   ├── LetterReferralObserver.php
│   │   └── TaskObserver.php
│   │
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── Filament/
│           └── AdminPanelProvider.php
│
├── database/
│   ├── migrations/
│   │   ├── 2025_02_15_100000_create_departments_table.php
│   │   ├── 2025_02_15_100001_add_role_and_department_to_users_table.php
│   │   ├── 2025_02_15_100002_create_letters_table.php
│   │   ├── 2025_02_15_100003_create_letter_referrals_table.php
│   │   ├── 2025_02_15_100004_create_tasks_table.php
│   │   ├── 2025_02_15_100005_create_attachments_table.php
│   │   ├── 2025_02_15_100006_create_comments_table.php
│   │   ├── 2025_02_15_100007_create_notifications_table.php
│   │   ├── 2025_02_15_100008_add_uuid_to_key_entities.php
│   │   ├── 2025_02_15_100009_add_soft_deletes_to_auditable_tables.php
│   │   └── 2025_02_15_100010_create_audit_logs_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── DepartmentSeeder.php
│       ├── UserSeeder.php
│       └── LetterSeeder.php              # نامه‌ها + ارجاعات + وظایف + نظرات + پیوست‌ها
│
├── lang/
│   └── fa.json                            # ترجمه‌های فارسی
│
├── resources/
│   └── views/
│       └── filament/
│           └── widgets/
│               └── urgent-items-widget.blade.php
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── FILAMENT_SETUP.md
├── ROUTES_AND_NOTIFICATIONS.md
└── PROJECT_STRUCTURE.md                  # این فایل
```

---

## تفکیک ماژول‌ها (منطقی)

| ماژول | مدل‌ها | Filament | کنترلرها / مسیرها | اعلان‌ها |
|-------|--------|----------|-------------------|----------|
| **کاربران و واحدها** | User, Department | UserResource, DepartmentResource | — | — |
| **نامه‌ها** | Letter, Attachment | LetterResource, AttachmentsRelationManager | AttachmentController (دانلود), Api\LetterController | — |
| **ارجاعات** | LetterReferral | LetterReferralResource, ReferralsRelationManager | — | LetterReferredNotification |
| **وظایف** | Task | TaskResource, TasksRelationManager | Api\TaskController | TaskAssignedNotification |
| **نظرات** | Comment | (در Letter/Task به‌صورت رابطه) | — | — |
| **اعلان‌ها** | (جدول notifications) | (نمایش در پنل) | NotificationController | — |
| **تاریخچه / audit** | AuditLog | (قابل افزودن AuditLogResource) | — | — |

---

## امکانات MVP پیاده‌سازی‌شده

- **Soft deletes:** واحد، نامه، ارجاع، وظیفه، نظر، پیوست.
- **Audit log:** برای همان موجودیت‌ها (created / updated / deleted / restored) با user_id، old_values، new_values، ip، user_agent.
- **دادهٔ دمو:** سیدرهای فارسی برای واحدها، کاربران، نامه‌ها، ارجاعات، وظایف، نظرات و پیوست‌ها.
- **زبان و جهت:** همه برچسب‌ها و متن‌های UI و اعلان‌ها فارسی و پنل RTL.

---

## دستورات برای دمو

```bash
# نصب وابستگی‌ها و تنظیم Laravel (در صورت نیاز)
composer install
cp .env.example .env
php artisan key:generate

# پایگاه‌داده و دادهٔ نمونه
php artisan migrate --seed

# لینک ذخیره‌سازی برای پیوست‌ها
php artisan storage:link
```

ورود دمو: `admin@demo.local` / `password` (و سایر کاربران سیدرشده با پسورد `password`).

---

## گسترش‌پذیری (آینده)

- **Workflow:** ماژول جدا برای تعریف مراحل و قوانین.
- **گزارش‌گیری:** ماژول Reports با کوئری روی Letter, Task, AuditLog.
- **چند شرکتی:** scope بر اساس tenant و جداول/ستون‌های مرتبط.
- **اپ موبایل:** همان APIهای فعلی با Sanctum و پاسخ‌های RTL-aware.
