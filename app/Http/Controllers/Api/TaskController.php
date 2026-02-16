<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * لیست وظایف (با فیلتر اختیاری برای مسئول).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::query()->with(['letter', 'assignedTo', 'createdBy']);

        if ($request->boolean('mine')) {
            $query->where('assigned_to_user_id', $request->user()->id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $tasks = $query->latest()->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $tasks->items(),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    /**
     * نمایش یک وظیفه.
     */
    public function show(Task $task): JsonResponse
    {
        $task->load(['letter', 'assignedTo', 'createdBy']);

        return response()->json(['data' => $task]);
    }
}
