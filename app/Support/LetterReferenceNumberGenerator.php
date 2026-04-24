<?php

namespace App\Support;

use App\Models\Letter;

class LetterReferenceNumberGenerator
{
    public static function generate(?string $type = null): string
    {
        $type = in_array($type, Letter::TYPES, true) ? $type : Letter::TYPE_INTERNAL;
        $year = explode('/', JalaliDate::format(now()), 2)[0];
        $prefix = $type === Letter::TYPE_EXTERNAL ? 'EXT' : 'INT';
        $pattern = sprintf('%s-%s-', $year, $prefix);

        $latestReference = Letter::query()
            ->withTrashed()
            ->where('reference_number', 'like', $pattern . '%')
            ->orderByDesc('id')
            ->value('reference_number');

        $nextSequence = 1;
        if (is_string($latestReference) && preg_match('/(\d+)$/', $latestReference, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%s-%04d', $year, $prefix, $nextSequence);
    }
}
