<?php

declare(strict_types=1);

namespace App\Http\Resources\IT;

use App\Models\CctvConcern;
use App\Models\CctvConcernItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of the CCTV Concern list (`it/cctv/index`), also used by its View / Edit dialog.
 *
 * @mixin CctvConcern
 */
final class CctvConcernRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'jo_no' => $this->jo_no,
            'reported_by' => $this->reported_by,
            'bus_body' => $this->bus?->body_number,
            'bus_detail' => $this->bus ? ($this->bus->plate_number ?? 'No plate').' - '.($this->bus->name ?? 'No name') : null,
            'bus_display' => $this->bus?->displayName(),
            'issue_type' => $this->issue_type,
            'problem_details' => $this->problem_details,
            'action_taken' => $this->action_taken,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to ? (string) $this->assigned_to : '',
            'assignee' => $this->assignee?->full_name,
            'items' => $this->usedItems->map(fn (CctvConcernItem $used): array => [
                'it_inventory_item_id' => (string) $used->it_inventory_item_id,
                'name' => $used->inventoryItem->item_name ?? 'Item',
                'qty_used' => (string) $used->qty_used,
                'remarks' => (string) ($used->remarks ?? ''),
            ])->values(),
        ];
    }
}
