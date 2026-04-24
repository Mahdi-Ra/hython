<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $authUser = auth()->user();

        $contacts = User::query()
            ->where('id', '!=', $authUser->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $selectedUser = null;
        $messages = collect();

        if ($request->filled('user')) {
            $selectedUser = User::query()->where('id', (int) $request->integer('user'))->first();
        }

        if (! $selectedUser && $contacts->isNotEmpty()) {
            $selectedUser = $contacts->first();
        }

        if ($selectedUser) {
            $messages = ChatMessage::query()
                ->with(['sender:id,name', 'receiver:id,name'])
                ->where(function ($query) use ($authUser, $selectedUser) {
                    $query->where('sender_id', $authUser->id)
                        ->where('receiver_id', $selectedUser->id);
                })
                ->orWhere(function ($query) use ($authUser, $selectedUser) {
                    $query->where('sender_id', $selectedUser->id)
                        ->where('receiver_id', $authUser->id);
                })
                ->orderBy('created_at')
                ->get();

            ChatMessage::query()
                ->where('sender_id', $selectedUser->id)
                ->where('receiver_id', $authUser->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return view('messages.index', compact('contacts', 'selectedUser', 'messages'));
    }

    public function show(User $user): View
    {
        abort_if($user->id === auth()->id(), 404);

        return $this->index(request()->merge(['user' => $user->id]));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $authUser = auth()->user();

        abort_if($user->id === $authUser->id, 404);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:102400'],
        ]);

        if (blank($validated['message'] ?? null) && ! $request->hasFile('attachment')) {
            throw ValidationException::withMessages([
                'message' => 'متن پیام یا فایل را وارد کنید.',
            ]);
        }

        $payload = [
            'sender_id' => $authUser->id,
            'receiver_id' => $user->id,
            'message' => filled($validated['message'] ?? null) ? $validated['message'] : null,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('chat-files');

            $payload['attachment_path'] = $path;
            $payload['attachment_name'] = $file->getClientOriginalName();
            $payload['attachment_mime'] = $file->getClientMimeType();
            $payload['attachment_size'] = $file->getSize();
        }

        ChatMessage::query()->create($payload);

        return redirect()->route('messages.show', $user)->with('success', 'پیام ارسال شد.');
    }

    public function download(ChatMessage $message): StreamedResponse
    {
        [$disk, $path] = $this->resolveMessageAttachment($message);

        $headers = [];
        if (filled($message->attachment_mime)) {
            $headers['Content-Type'] = $message->attachment_mime;
        }

        return $disk->download(
            $path,
            $message->attachment_name ?: basename($path),
            $headers
        );
    }

    public function preview(ChatMessage $message): Response
    {
        [$disk, $path] = $this->resolveMessageAttachment($message);

        return response()->file($disk->path($path), [
            'Content-Type' => $message->attachment_mime ?: $disk->mimeType($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . ($message->attachment_name ?: basename($path)) . '"',
        ]);
    }

    private function resolveMessageAttachment(ChatMessage $message): array
    {
        $authUserId = (int) auth()->id();

        abort_unless(
            in_array($authUserId, [(int) $message->sender_id, (int) $message->receiver_id], true),
            403
        );

        abort_unless(filled($message->attachment_path), 404);

        $disk = Storage::disk('local')->exists($message->attachment_path)
            ? Storage::disk('local')
            : Storage::disk('public');

        abort_unless($disk->exists($message->attachment_path), 404);

        return [$disk, $message->attachment_path];
    }
}
