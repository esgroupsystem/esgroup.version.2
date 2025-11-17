<?php

namespace App\Exports;

use App\Models\JobOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JobOrdersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return JobOrder::select(
            'id',
            'job_creator',
            'job_type',
            'job_status',
            'job_date_filled'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Creator',
            'Job Type',
            'Status',
            'Date Filled'
        ];
    }
}
