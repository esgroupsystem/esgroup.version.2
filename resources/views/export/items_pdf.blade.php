<!DOCTYPE html>
<html>
<head>
    <title>Product Stock Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #666; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>

<h2>Product Inventory Report</h2>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Unit</th>
            <th>Stock Qty</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($products as $p)
        <tr>
            <td>{{ $p->product_name }}</td>
            <td>{{ $p->category->name ?? '—' }}</td>
            <td>{{ $p->unit }}</td>
            <td>{{ $p->stock_qty ?? 0 }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
