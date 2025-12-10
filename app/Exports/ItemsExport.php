<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::with('category')->get()->map(function ($item) {
            return [
                'Product Name' => $item->product_name,
                'Category' => $item->category->name ?? '—',
                'Unit' => $item->unit,
                'Part Number' => $item->part_number,
                'Details' => $item->details,
                'Stock Qty' => $item->stock_qty ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Category',
            'Unit',
            'Part Number',
            'Details',
            'Stock Qty',
        ];
    }
}
