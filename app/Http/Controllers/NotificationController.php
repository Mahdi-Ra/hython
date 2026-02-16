<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * علامت‌گذاری یک اعلان به‌عنوان خوانده‌شده.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = Auth::user()
            ->unreadNotifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        $url = $this->getActionUrlFromNotification($notification);
        $redirect = $request->get('redirect');

        if ($redirect) {
            return redirect()->to($redirect);
        }
        if ($url) {
            return redirect()->to($url);
        }

        return redirect()->back()->with('success', __('اعلان به‌عنوان خوانده‌شده علامت‌گذاری شد.'));
    }

    /**
     * علامت‌گذاری همه اعلان‌های کاربر به‌عنوان خوانده‌شده.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', __('همه اعلان‌ها خوانده شدند.'));
    }

    private function getActionUrlFromNotification(DatabaseNotification $notification): ?string
    {
        $data = $notification->data;
        $actionUrl = $data['action_url'] ?? null;

        return is_string($actionUrl) ? $actionUrl : null;
    }
}
