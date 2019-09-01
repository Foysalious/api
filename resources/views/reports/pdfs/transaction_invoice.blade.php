<!DOCTYPE html>
<html lang="en">
<head>
    <title> Invoice </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: "Shonar Bangla";
            src: {{storage_path("Shonar Bangla.ttf")}} format("truetype"); /* IE9*/
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
        .invoice-table tr {
            color: #689ab8;
            font-size: 14px;
        }
        .invoice-table tr img {}
        .terms {
            font-size: 12px;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
            color: #4a4a4a;
        }
        body {
            font-family: DejaVu Sans, 'Shonar Bangla', 'Roboto', sans-serif;
        }
    </style>
</head>

<body>
<table align="center" style="max-width: 800px;margin: auto;min-width: 600px;font-family: Arial, sans-serif">
    <tbody>
    <tr style="border-bottom: 1px solid #ddd">
        <td>
            <table style="width: 100%;border-bottom: 1px solid #ddd">
                @if(isset($payment_receiver))
                    <tr>
                        <td width="120"><img style="max-width: 120px" src="{{$payment_receiver['image']}}" alt=""></td>
                        <td colspan="3">
                            <div style="text-align: left;padding: 10px 20px;">
                                <span style="color: #383d46;letter-spacing: -0.05px;font-weight: 700;">{{ucfirst($payment_receiver['name'])}}</span><br>
                                <span style="color: #9b9b9b;">{{$payment_receiver['mobile']}}</span><br>
                                <span style="color: #9b9b9b;">{{$payment_receiver['address']}}</span>
                            </div>
                        </td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%;border-bottom: 1px solid #ddd;padding-bottom: 4px">
                <tr>
                    <td colspan="3" style="border-left: 4px solid #1b4280">
                        @if(isset($user))
                            <div style="padding: 10px 20px">
                                <span style="color: #B0BEC5;">Bill to</span><br>
                                <span style="color: #383d46;letter-spacing: -0.05px;font-weight: 700;">{{ucfirst($user['name'])}}</span><br>
                                <span style="color: #9b9b9b">{{$user['mobile']}}</span>
                            </div>
                        @endif
                    </td>
                    <td align="right">
                        <div style="text-align: right">
                            <span style="color: #383d46;letter-spacing: -0.05px;font-weight: 700;">
                                <span style="width: 13px">@include('reports.pdfs.taka_sign')</span> {{number_format($amount,2)}}
                            </span><br>
                            <span style="color: #9b9b9b;">{{$created_at}}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    @if(isset($pos_order))
        <tr>
            <td>
                <table style="border-bottom: 1px solid #ddd;width: 100%;color: #929292" cellspacing="5">
                    <thead>
                    <tr>
                        <th style="color: #9b9b9b">Product Name</th>
                        <th style="color: #9b9b9b">Quantity</th>
                        <th style="color: #9b9b9b">Unit Price</th>
                        <th style="color: #9b9b9b" align="right">Total Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $subtotal = 0;?>
                    @foreach($pos_order['items'] as $item)
                        <tr>
                            <td style="color: #383d46">
                                {{ $item->service_name }} <br>
                                @if($item->warranty)
                                    <span style="font-size: 10px">* {{ $item->warranty }} {{ $item->warranty_unit }} warranty applied.</span>
                                @endif
                            </td>
                            <td style="color: #383d46">{{$item->quantity}}</td>
                            <td style="color: #383d46;">
                                <span style="width: 11px">
                                    @include('reports.pdfs.taka_sign')
                                </span>
                                {{number_format($item->unit_price,2)}}</td>
                            <?php $total = (double)$item->quantity * (double)$item->unit_price; $subtotal += $total;?>
                            <td align="right" style="color: #383d46"><span style="width: 13px">@include('reports.pdfs.taka_sign')</span> {{number_format($total,2)}}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="border-top-color: #9b9d9b;border-top-width: 1px ;border-top-style: solid;mso-border-top-width-alt: 1px;color: #9b9b9b" bordercolor="#9b9d9b" colspan="3" align="right">Total</td>
                        <td style="border-top-color: #9b9d9b;border-top-width: 1px ;border-top-style: solid;mso-border-top-width-alt: 1px;color: #383d46" bordercolor="#9b9d9b" align="right">
                            <span style="width: 11px">@include('reports.pdfs.taka_sign')</span> {{number_format($pos_order['total'],2)}}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" style="color: #9b9b9b">Total Vat</td>
                        <td align="right" style="color: #383d46"><span style="width: 13px">@include('reports.pdfs.taka_sign')</span> {{number_format($pos_order['vat'],2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" style="color: #9b9b9b">Total Discount</td>
                        <td align="right" style="color: #383d46"><span style="width: 13px">@include('reports.pdfs.taka_sign')</span> {{number_format($pos_order['discount'],2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" style="color: #9b9b9b">Total Payable</td>
                        <td align="right" style="color: #383d46"><span style="width: 13px">@include('reports.pdfs.taka_sign')</span> {{number_format($pos_order['grand_total'],2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" style="color: #9b9b9b">Paid</td>
                        <td align="right" style="color: #383d46"><span style="width: 13px">@include('reports.pdfs.taka_sign')</span> {{number_format($pos_order['paid'],2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" style="color: #9b9b9b">Due</td>
                        <td align="right" style="color: #383d46"><span style="width: 13px">@include('reports.pdfs.taka_sign')</span> {{number_format($pos_order['due'],2)}}</td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    @endif

    @if(isset($method))
    <tr>
        <td>
            <table style="width: 100%;color: #383d46;" class="invoice-table" cellpadding="5">
                <tr>
                    <td style="width: 20px">
                        <img src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/icons/bag.png"/>
                    </td>
                    <td style="color: #7e848c;">Payment amount</td>
                    <td style="color: #383d46;" align="right"><span style="width: 13px">
                            @include('reports.pdfs.taka_sign'){{number_format($amount,2)}}</span>
                    </td>
                </tr>
                <tr>
                    <td><img src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/icons/info.png"/></td>
                    <td style="color: #7e848c;">Payment purpose</td>
                    <td style="color: #383d46;" align="right">{{$description}}</td>
                </tr>
                <tr>
                    <td><img src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/icons/check.png"/></td>
                    <td style="color: #7e848c;">Payment time</td>
                    <td style="color: #383d46;" align="right">{{$created_at}}</td>
                </tr>
                <tr>
                    <td><img src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/icons/credit.png"/></td>
                    <td style="color: #7e848c;">Payment type</td>
                    <td style="color: #383d46;" align="right">{{$method}}</td>
                </tr>
            </table>
        </td>
    </tr>
    @endif
    <tr>
        <td>
            <p class="terms">
                <strong>Terms and Note:</strong>
                If needed, it can take a maximum of 15 days to get your refund. You will only get return by the way you pay.
            </p>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
