<?php

declare(strict_types=1);

namespace App\Services\ITDepartment;

use App\Enums\CctvConcernStatus;
use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Models\ItInventoryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class CctvConcernDirectoryService
{
    /** @return array<string, mixed> */
    public function indexData(string $search, string $status): array
    {
        $buses = BusDetail::query()
            ->orderBy('body_number')
            ->get(['id', 'garage', 'name', 'body_number', 'plate_number'])
            ->map(function (BusDetail $bus): BusDetail {
                $bus->setAttribute('display_name', implode(' - ', array_filter([
                    $bus->body_number, $bus->plate_number, $bus->name, $bus->garage,
                ])));

                return $bus;
            });

        $baseQuery = $this->query($search, $status);
        $jobOrders = (clone $baseQuery)->latest()->paginate(10)->withQueryString();
        $allJobOrders = (clone $baseQuery)->get();
        $statusCounts = $allJobOrders->groupBy('status')->map->count();
        $issueCounts = $allJobOrders->groupBy('issue_type')->map->count()->sortDesc();
        $partCounts = $allJobOrders
            ->flatMap(fn ($job) => $job->usedItems->map(fn ($used) => $used->inventoryItem->item_name ?? null))
            ->filter()->groupBy(fn ($name) => $name)->map->count()->sortDesc();
        $assigneeCounts = $allJobOrders
            ->map(fn (CctvConcern $job): ?string => $job->assignee?->full_name)
            ->filter()->groupBy(fn ($name) => $name)->map->count()->sortDesc();

        return [
            'jobOrders' => $jobOrders,
            'allJobOrders' => $allJobOrders,
            'agents' => User::query()->where('role', 'IT Officer')->orderBy('full_name')->get(),
            'buses' => $buses,
            'busDisplayMap' => $buses->pluck('display_name', 'id'),
            'inventoryItems' => ItInventoryItem::query()->active()->orderBy('item_name')->get([
                'id', 'item_name', 'category', 'brand', 'model', 'unit', 'stock_qty', 'location',
            ]),
            'statusOptions' => CctvConcernStatus::options(),
            'statusClasses' => [
                'Open' => 'badge-subtle-warning',
                'In Progress' => 'badge-subtle-info',
                'Fixed' => 'badge-subtle-success',
                'Closed' => 'badge-subtle-secondary',
            ],
            'totalOrders' => $allJobOrders->count(),
            'openCount' => $statusCounts['Open'] ?? 0,
            'progressCount' => $statusCounts['In Progress'] ?? 0,
            'fixedCount' => $statusCounts['Fixed'] ?? 0,
            'closedCount' => $statusCounts['Closed'] ?? 0,
            'topIssue' => $issueCounts->keys()->first(),
            'topIssueCount' => $issueCounts->first() ?? 0,
            'topPart' => $partCounts->keys()->first(),
            'topPartCount' => $partCounts->first() ?? 0,
            'topAssignee' => $assigneeCounts->keys()->first(),
            'topAssigneeCount' => $assigneeCounts->first() ?? 0,
        ];
    }

    /** @return Builder<CctvConcern> */
    public function query(string $search = '', string $status = ''): Builder
    {
        return CctvConcern::query()
            ->with([
                'bus:id,garage,name,body_number,plate_number',
                'assignee:id,full_name',
                'usedItems.inventoryItem:id,item_name,unit,brand,model',
            ])
            ->search($search)
            ->status($status);
    }
}
