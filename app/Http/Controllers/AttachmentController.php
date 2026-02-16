<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * دانلود پیوست با احراز دسترسی (فقط اگر کاربر به نامه دسترسی دارد).
     */
    public function download(Request $request, Attachment $attachment): StreamedResponse|never
    {
        $this->authorizeDownload($request->user(), $attachment);

        $path = $attachment->path;
        if (! Storage::disk('public')->exists($path)) {
            abort(404, __('فایل یافت نشد.'));
        }

        return Storage::disk('public')->download(
            $path,
            $attachment->name,
            [
                'Content-Type' => $attachment->mime_type ?? 'application/octet-stream',
            ]
        );
    }

    private function authorizeDownload($user, Attachment $attachment): void
    {
        $letter = $attachment->letter;

        // ایجادکننده نامه، گیرنده ارجاع، یا مدیر سیستم
        $isCreator = $letter->user_id === $user->id;
        $hasReferral = $letter->referrals()->where('to_user_id', $user->id)->exists();
        $isAdmin = $user->role === \App\Models\User::ROLE_ADMIN;

        if ($isCreator || $hasReferral || $isAdmin) {
            return;
        }

        abort(403, __('شما مجوز دانلود این پیوست را ندارید.'));
    }
}
