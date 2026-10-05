<?php

declare(strict_types=1);

namespace App\Http\Resources\IT;

use App\Models\CctvConcern;
use App\Models\CctvConcernItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One CCTV concern for the print page (`it/cctv/print`) and the CSV download.
 *
 * @mixin CctvConcern
 */
final class CctvConcernExportResource extends JsonResource
{
    public const CSV_HEADINGS = [
        'JO No', 'Bus Details', 'Reporter', 'Issue Type', 'Problem Details', 'Action Taken',
        'Status', 'Assignee', 'Items Used', 'Created Date', 'Fixed Date',
    ];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bus' => $this->busText(),
            'reporter' => $this->reported_by,
            'issue' => (string) $this->issue_type,
            'details' => $this->problem_details,
            'status' => (string) $this->status,
            'date' => $this->created_at?->format('Y-m-d h:i A'),
        ];
    }

    /** @return list<mixed> one CSV line, in CSV_HEADINGS order */
    public function toCsvRow(): array
    {
        return [
            $this->jo_no,
            $this->busText(),
            $this->reported_by,
            $this->issue_type,
            $this->problem_details,
            $this->action_taken,
            $this->status,
            $this->assignee?->full_name,
            $this->usedItems
                ->map(fn (CctvConcernItem $used): string => trim(($used->inventoryItem->item_name ?? 'Item').' x'.$used->qty_used.' '.($used->inventoryItem->unit ?? '')))
                ->implode(', '),
            $this->created_at?->format('Y-m-d h:i A'),
            $this->fixed_at?->format('Y-m-d h:i A'),
        ];
    }

    /** "Body - Plate - Name", or the stored bus id when the bus is gone. */
    private function busText(): string
    {
        return $this->bus ? $this->bus->displayName(withGarage: false) : (string) $this->bus_no;
    }
}
