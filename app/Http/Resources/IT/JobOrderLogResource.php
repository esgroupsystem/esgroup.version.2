<?php

declare(strict_types=1);

namespace App\Http\Resources\IT;

use App\Models\JobOrderLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One activity log entry on the job order detail page. `meta` lists each key
 * with either `old`/`new` (a changed field) or a plain `value`.
 *
 * @mixin JobOrderLog
 */
final class JobOrderLogResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $meta = is_string($this->meta) ? (json_decode($this->meta, true) ?: []) : (is_array($this->meta) ? $this->meta : []);
        $text = static fn (mixed $value): string => is_scalar($value) || $value === null ? (string) ($value ?? 'N/A') : (string) json_encode($value);

        return [
            'id' => $this->id,
            'user' => $this->user?->full_name ?? 'System',
            'action' => ucfirst(str_replace('_', ' ', (string) $this->action)),
            'date' => $this->created_at?->format('M d, Y'),
            'time' => $this->created_at?->format('h:i A'),
            'meta' => collect($meta)->map(function (mixed $value, int|string $key) use ($text): array {
                $change = is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value);

                return [
                    'key' => ucfirst(str_replace('_', ' ', (string) $key)),
                    'old' => $change ? $text($value['old']) : null,
                    'new' => $change ? $text($value['new']) : null,
                    'value' => $change ? null : $text($value),
                ];
            })->values(),
        ];
    }
}
