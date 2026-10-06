<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Http\Resources\Concerns\FormatsDates;
use App\Models\PayrollSettingVersion;
use App\Support\Payroll\PayrollSettingCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A Payroll Settings version for the list. Wrap null for the blank "new version" form.
 *
 * @mixin PayrollSettingVersion
 *
 * @property-read PayrollSettingVersion|null $resource
 */
final class PayrollSettingVersionResource extends JsonResource
{
    use FormatsDates;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $version = $this->resource;

        return [
            'id' => $version?->id,
            'label' => (string) ($version->label ?? ''),
            'effective_from' => $version?->effective_from?->toDateString() ?? '',
            'effective_label' => $version?->effective_from?->format('M d, Y'),
            'notes' => (string) ($version->notes ?? ''),
            'values' => PayrollSettingCatalog::normalize((array) ($version->values ?? [])),
            'created_by' => $version?->relationLoaded('creator') ? ($version->creator?->full_name ?: $version->creator?->username) : null,
            'updated_by' => $version?->relationLoaded('updater') ? ($version->updater?->full_name ?: $version->updater?->username) : null,
            'updated_at' => $this->formatDate($version?->updated_at, 'M d, Y h:i A'),
        ];
    }
}
