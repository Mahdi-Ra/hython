<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->attachment_mime, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with((string) $this->attachment_mime, 'video/');
    }

    public function isAudio(): bool
    {
        return str_starts_with((string) $this->attachment_mime, 'audio/');
    }

    public function isPdf(): bool
    {
        return $this->attachment_mime === 'application/pdf';
    }

    public function canPreview(): bool
    {
        return $this->isImage() || $this->isVideo() || $this->isAudio() || $this->isPdf();
    }
}
