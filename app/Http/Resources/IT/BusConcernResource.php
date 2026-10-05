<?php

declare(strict_types=1);

namespace App\Http\Resources\IT;

use App\Enums\CctvConcernStatus;
use App\Models\CctvConcern;
use App\Models\CctvConcernItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One concern in the active / completed tables of the bus detail page (`it/cctv/bus-status-show`).
 *
 * @mixin CctvConcern
 */
final class BusConcernResource extends JsonResource
{
    /** In Progress for longer than this many days shows as overdue. */
    private const OVERDUE_DAYS = 3;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'jo_no' => $this->jo_no,
            'issue_type' => $this->issue_type,
            'status' => $this->status,
            'overdue' => $this->status === CctvConcernStatus::InProgress->value
                && $this->created_at
                && $this->created_at->lt(now()->subDays(self::OVERDUE_DAYS)),
            'assignee' => $this->assignee?->full_name,
            'problem_details' => $this->problem_details,
            'action_taken' => $this->action_taken,
            'parts' => $this->usedItems
                ->map(fn (CctvConcernItem $used): string => ($used->inventoryItem->item_name ?? 'Item').' x'.$used->qty_used)
                ->values(),
        ];
    }
}
