# راهنمای راه‌اندازی مسیرها و اعلان‌ها

## ثبت سرویس‌پروایدرها

در `bootstrap/providers.php` (Laravel 11) این موارد را اضافه کنید:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
];
```

`AppServiceProvider` ناظران (observers) ارجاع نامه و وظیفه را ثبت می‌کند تا اعلان‌ها هنگام ایجاد ارجاع یا محول شدن وظیفه ارسال شوند.

## مسیرهای وب (نیاز به ورود)

| مسیر | متد | کنترلر | توضیح |
|------|-----|--------|--------|
| `/attachments/{attachment}/download` | GET | AttachmentController@download | دانلود پیوست (با احراز دسترسی) |
| `/notifications/{id}/read` | POST | NotificationController@markAsRead | علامت‌گذاری یک اعلان به‌عنوان خوانده‌شده |
| `/notifications/read-all` | POST | NotificationController@markAllAsRead | خوانده‌شدن همه اعلان‌ها |

همه این مسیرها داخل `auth` هستند.

## مسیرهای API (نیاز به Sanctum)

پیش‌وند: `/api/v1`

| مسیر | متد | توضیح |
|------|-----|--------|
| `GET /letters` | - | لیست نامه‌ها (پارامترهای اختیاری: `mine`, `status`, `per_page`) |
| `GET /letters/{letter}` | - | جزئیات یک نامه (با uuid) |
| `GET /tasks` | - | لیست وظایف (پارامترهای اختیاری: `mine`, `status`, `per_page`) |
| `GET /tasks/{task}` | - | جزئیات یک وظیفه (با uuid) |

برای استفاده در اپ موبایل یا کلاینت خارجی، احراز هویت با Laravel Sanctum انجام می‌شود.

## اعلان‌ها (فارسی و RTL)

- **LetterReferredNotification**: هنگام ایجاد ارجاع نامه برای کاربر، به او اعلان داده می‌شود. متن و لینک به نامه در دیتابیس ذخیره می‌شود و در پنل با `rtl: true` نمایش داده می‌شود.
- **TaskAssignedNotification**: هنگام ایجاد یا تغییر مسئول وظیفه، به کاربر محول‌شده اعلان داده می‌شود.

هر دو از صف (queue) پشتیبانی می‌کنند (`ShouldQueue`). برای ارسال فوری بدون صف، `implements ShouldQueue` را از کلاس اعلان حذف کنید.

## زبان و RTL

برای نمایش پیام‌های کنترلرها به فارسی، در `config/app.php` مقدار `locale` را `fa` قرار دهید. ترجمه‌های مورد نیاز در `lang/fa.json` قرار دارند.
