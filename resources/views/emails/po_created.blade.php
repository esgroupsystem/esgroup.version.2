@php($version = time())
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Purchase Order Notification</title>
</head>

<body style="margin:0; padding:0; background:#e9ecef; font-family:Arial, Helvetica, sans-serif;">

    <table width="100%" cellspacing="0" cellpadding="0" style="padding:20px 0;">
        <tr>
            <td align="center">

                <!-- PO Receipt Container -->
                <table width="460" cellspacing="0" cellpadding="0"
                    style="background:#ffffff; border:1px solid #dcdcdc; border-radius:6px;">

                    <!-- Header -->
                    <tr>
                        <td style="text-align:center; padding:12px; background:#0a4fa3;">
                            <img src="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}" width="75"
                                style="margin-bottom:6px;">
                            <h3 style="color:#ffffff; margin:0; font-size:16px;">Purchase Order Created</h3>
                            <p style="color:#e9e9e9; margin:3px 0 0; font-size:12px;">
                                PO #: <strong>{{ $po->po_number }}</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:15px 18px; font-size:13px; color:#333;">

                            <!-- Summary Box -->
                            <table width="100%" cellspacing="0" cellpadding="0"
                                style="border:1px solid #ccc; padding:10px; border-radius:4px; margin-bottom:12px;">
                                <tr>
                                    <td style="font-weight:bold;">Garage:</td>
                                    <td style="text-align:right;">{{ $po->garage }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">Status:</td>
                                    <td style="text-align:right; color:#d08800;">{{ $po->status }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">Date Created:</td>
                                    <td style="text-align:right;">{{ $po->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>

                            <!-- Requester Info -->
                            <h4 style="margin:10px 0 4px; font-size:14px; border-bottom:1px dashed #bbb;">
                                Request Information
                            </h4>

                            <table width="100%" cellspacing="0" cellpadding="4" style="font-size:13px;">
                                <tr>
                                    <td><strong>Requested By:</strong></td>
                                    <td align="right">{{ $po->requester->full_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td align="right">{{ $po->requester->email }}</td>
                                </tr>
                            </table>

                            <!-- Item List -->
                            <h4 style="margin:12px 0 6px; font-size:14px; border-bottom:1px dashed #bbb;">
                                Item Details
                            </h4>

                            <table width="100%" cellspacing="0" cellpadding="6"
                                style="font-size:12px; border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#0a4fa3; color:#fff;">
                                        <th align="left">Category</th>
                                        <th align="left">Product</th>
                                        <th align="center">Unit</th>
                                        <th align="center">Qty</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($po->items as $item)
                                        <tr style="background:#f7f7f7; border-bottom:1px solid #ddd;">
                                            <td>{{ $item->product->category->name ?? '—' }}</td>
                                            <td>{{ $item->product->product_name }}</td>
                                            <td align="center">{{ $item->product->unit ?? '—' }}</td>
                                            <td align="center" style="font-weight:bold;">{{ $item->qty }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#0a4fa3; text-align:center; padding:8px;">
                            <p style="font-size:11px; color:#e9e9e9; margin:0;">
                                ES Group System • {{ date('Y') }}
                                <br>Automated Email — Do Not Reply
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- End PO Receipt -->

            </td>
        </tr>
    </table>

</body>

</html>
