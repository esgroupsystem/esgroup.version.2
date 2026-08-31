<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\JobOrderMaintenance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class JobOrderMaintenanceDirectoryService
{
    /** @return array{search:string,status:string,bus_id:?int,date_filter:string,filter_date:string,filter_month:string,filter_year:string} */
    public function filters(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'bus_id' => $request->integer('bus_id') ?: null,
            'date_filter' => $request->string('date_filter')->toString(),
            'filter_date' => $request->string('filter_date')->toString(),
            'filter_month' => $request->string('filter_month')->toString(),
            'filter_year' => $request->string('filter_year')->toString(),
        ];
    }

    /** @param Builder<JobOrderMaintenance> $query */
    public function apply(Builder $query, array $filters, bool $includeStatus = true): Builder
    {
        return $query
            ->search($filters['search'])
            ->when($includeStatus && filled($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(filled($filters['bus_id']), fn (Builder $query) => $query->where('bus_id', $filters['bus_id']))
            ->tap(fn (Builder $query) => $this->applyDateFilter($query, $filters));
    }

    /** @param Builder<JobOrderMaintenance> $query */
    private function applyDateFilter(Builder $query, array $filters): void
    {
        if ($filters['date_filter'] === 'day' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['filter_date'])) {
            $query->whereDate('created_at', $filters['filter_date']);
        }

        if ($filters['date_filter'] === 'month' && preg_match('/^\d{4}-\d{2}$/', $filters['filter_month'])) {
            [$year, $month] = explode('-', $filters['filter_month']);
            $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }

        if ($filters['date_filter'] === 'year' && preg_match('/^\d{4}$/', $filters['filter_year'])) {
            $query->whereYear('created_at', $filters['filter_year']);
        }
    }
}
