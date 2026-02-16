# راهنمای راه‌اندازی Filament (پنل ادمین)

## ثبت پنل

در پروژه Laravel، در فایل `bootstrap/providers.php` این خط را اضافه کنید:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
];
```

## راست‌به‌چپ (RTL) و زبان فارسی

- در `config/app.php` مقدار `locale` را روی `fa` قرار دهید تا پیام‌های سیستمی فارسی شوند.
- پنل با `->direction('rtl')` در `AdminPanelProvider` برای RTL پیکربندی شده است. اگر در نسخه Filament شما متد `direction` وجود نداشت، آن خط را حذف کنید و به‌جای آن در قالب اصلی پنل، به تگ `<html>` ویژگی `dir="rtl"` اضافه کنید.

## پیوست‌های نامه (Attachments)

برای ذخیره فایل‌های پیوست، لینک symbolic ذخیره‌سازی را ایجاد کنید:

```bash
php artisan storage:link
```

فایل‌ها در `storage/app/public/letter-attachments` ذخیره می‌شوند.

## ترجمه‌های Filament

برای ترجمه برچسب‌های پیش‌فرض Filament (مثل «ذخیره»، «ایجاد»، «حذف») به فارسی، ترجمه‌های پنل را منتشر و سپس ویرایش کنید:

```bash
php artisan vendor:publish --tag=filament-panels-translations
```

سپس فایل‌های داخل `lang/` را برای زبان `fa` پر کنید.
