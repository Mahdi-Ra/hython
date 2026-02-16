<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Letter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LetterController extends Controller
{
    /**
     * لیست نامه‌ها (با فیلتر اختیاری برای کاربر جاری).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Letter::query()->with(['user', 'department']);

        if ($request->boolean('mine')) {
            $query->where('user_id', $request->user()->id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $letters = $query->latest()->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $letters->items(),
            'meta' => [
                'current_page' => $letters->currentPage(),
                'last_page' => $letters->lastPage(),
                'per_page' => $letters->perPage(),
                'total' => $letters->total(),
            ],
        ]);
    }

    /**
     * نمایش یک نامه.
     */
    public function show(Letter $letter): JsonResponse
    {
        $letter->load(['user', 'department', 'referrals.fromUser', 'referrals.toUser', 'tasks', 'attachments']);

        return response()->json(['data' => $letter]);
    }
}
