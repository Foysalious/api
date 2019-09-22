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

        .invoice-table tr img {
        }

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

                @if(isset($partner))
                    <tr>
                        <td width="120"><img style="max-width: 120px" src="{{$partner['image']}}" alt=""></td>
                        <td colspan="3">
                            <div style="text-align: left;padding: 10px 20px;">
                                <span style="color: #383d46;letter-spacing: -0.05px;font-weight: 700;">{{ucfirst($partner['name'])}}</span><br>
                                <span style="color: #9b9b9b;">{{$partner['mobile']}}</span><br>
                                <span style="color: #9b9b9b;">{{$partner['email']}}</span><br>
                                <span style="color: #9b9b9b;">{{$partner['address']}}</span>
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
                        @if(isset($customer))
                            <div style="padding: 10px 20px">
                                <span style="color: #B0BEC5;">Bill to</span><br>
                                <span style="color: #383d46;letter-spacing: -0.05px;font-weight: 700;">{{ucfirst($customer['name'])}}</span><br>
                                <span style="color: #9b9b9b">{{$customer['mobile']}}</span>
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    @if(isset($orders))
        <tr>
            <td>
                <table style="border-bottom: 1px solid #ddd;width: 100%;color: #929292" cellspacing="5">
                    <thead>
                    <tr>
                        <th style="color: #9b9b9b">Service Name</th>
                        <th style="color: #9b9b9b">Order Code</th>
                        <th style="color: #9b9b9b">QTY</th>
                        <th style="color: #9b9b9b">Unit Price</th>
                        <th style="color: #9b9b9b" align="right">Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td style="color: #383d46"> {{ $order['service_name'] }} <br> </td>
                            <td style="color: #383d46">{{$order['id']}}</td>
                            <td style="color: #383d46">{{$order['service_quantity']}}</td>
                            <td style="color: #383d46">{{$order['service_unit_price']}}</td>
                            <td align="right" style="color: #383d46"><span style="width: 13px"></span> {{number_format(($order['total']), 2)}}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" align="right" style="color: #9b9b9b">Total Service Price</td>
                        <td align="right" style="color: #383d46"><span style="width: 13px"></span> {{number_format($original_price,2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="4" align="right" style="color: #9b9b9b">Discount</td>
                        <td align="right" style="color: #383d46"><span style="width: 13px"></span> {{number_format($discount,2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="4" align="right" style="color: #9b9b9b">Subtotal</td>
                        <td align="right" style="color: #383d46"><span style="width: 13px"></span> {{number_format($subtotal,2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="4" align="right" style="color: #9b9b9b">Grand Total</td>
                        <td align="right" style="color: #383d46"><span style="width: 13px"></span> {{number_format($subtotal,2)}}</td>
                    </tr>
                    </tbody>
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