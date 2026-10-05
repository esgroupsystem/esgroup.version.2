<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\JobOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/** Tickets Job Order Excel download; rows come from JobOrderService::excelExportRows(). */
final class JobOrdersExport implements FromCollection, WithHeadings
{
    /** @param Collection<int, JobOrder> $rows */
    public function __construct(
        private readonly Collection $rows,
    ) {}

    /** @return Collection<int, JobOrder> */
    public function collection(): Collection
    {
        return $this->rows;
    }

    /** @return list<string> */
    public function headings(): array
    {
        return ['ID', 'Creator', 'Job Type', 'Status', 'Date Filled'];
    }
}
