<!DOCTYPE html>
<html lang="en">
<head>
    <title> Invoice </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'kalpurush', sans-serif!important;
            font-size: 14px !important;
        }
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

        .payment-table{
            width: 100%;
            color: #383d46;
            background: #F8F8F8;
            padding: 15px;
        }

        .payment-table td{
            /*width:20%;*/
            font-style: normal;
            font-weight: normal;
            font-size: 15px;
            color: #505050;
        }

        .terms {
            font-size: 12px;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
            color: #4a4a4a;
        }

        .note{
            padding-top: 10px;
            padding-left: 45px;
            padding-right: 45px;
            font-style: normal;
            font-weight: 500;
            font-size: 12px;
            line-height: 13px;
            color: #4A4A4A;
        }
        body {
            font-family: DejaVu Sans, 'Shonar Bangla', 'Roboto','kalpurush','Siyamrupali', sans-serif;
        }
        @if(isset($method))
            footer {
                position: fixed;
                background-color: #F1F1F1;
                bottom: 0cm;
                left: 0cm;
                right: 0cm;
                height: 1.5cm;
                background-color: #F1F1F1;
            }
            @page {
                /*size: a4 landscape;*/
                margin:0.9;
                padding:0.9;
            }
        @endif
    </style>
</head>

<body>
<table align="center" style="max-width: 800px;margin: auto;min-width: 600px;font-family: Arial, sans-serif">
    <tbody>
    <tr style="border-bottom: 1px solid #ddd">
        <td>
            <table style="width: 100%; @if(isset($method)) padding:10px; @else border-bottom: 1px solid #ddd @endif">
                @if(isset($payment_receiver))
                    <tr>
                        <td @if(isset($method))style="padding-left: 25px;" width="80" @else width="120" @endif><img style="max-width: 120px" src="{{$payment_receiver['image']}}" alt=""></td>
                        <td colspan="3">
                            <div style="text-align: left;padding: 10px 20px;">
                                {{ucfirst($payment_receiver['name'])}}<br>
                                <span style="color: #9b9b9b;">{{$payment_receiver['mobile']}}</span><br>
                                <span style="color: #9b9b9b;">{{$payment_receiver['address']}}</span>
                            </div>
                        </td>
                        @if(isset($method))
                            <td width="80"><img width="70px" height="70px" src="https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/training_video_images/banners/1586691528_tiwnn.png" alt="">

                            </td>

                        @endif
                    </tr>
                @endif
            </table>
        </td>
    </tr>

    @if(isset($pos_order))
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
                    @if(isset($pos_order['delivery_charge']))
                        <tr>
                            <td colspan="3" align="right" style="color: #9b9b9b">Delivery Charge</td>
                            <td align="right" style="color: #383d46"><span style="width: 13px">@include('reports.pdfs.taka_sign')</span> {{number_format($pos_order['delivery_charge'],2)}}</td>
                        </tr>
                    @endif
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

        <!--<tr>
            <td>
                <p class="terms">
                    <strong>Terms and Note:</strong>
                    If needed, it can take a maximum of 15 days to get your refund. You will only get return by the way you pay.
                </p>
            </td>
        </tr>-->

    @endif

    @if(isset($method))
    <tr>
        <td>
            <table style="width: 100%; padding:10px; background-color: #F1F1F1; color: #9b9b9b;">
                <tr>
                    <td style="padding-left: 25px" colspan="3">
                        <div>
                            Payment ID : @if(isset($payment_id))<span style="padding-top: 10px; color: #383D46;">#P-{{$payment_id}}</span> @endif
                        </div>
                    </td>
                    <td style="padding-right: 20px" align="right">
                        <div style="text-align: right">
                            Date: <span style="color: #383D46;">{{$created_at_date}}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td align="center; padding: 20px;"><span style="font-style: normal; font-weight: 500; font-size: 20px; color: #000000;">Money Receipt</span></td>
    </tr>


    <tr>
        <td style="padding: 0px 40px;">
            <table class="payment-table" cellpadding="5">
                <tr>
                    <td>Collection Time</td>
                    <td style="color: #383d46;">{{$created_at}}</td>
                </tr>
                <tr>
                    <td>Payment type</td>
                    <td style="color: #383d46;">{{$method}}</td>
                </tr>
                <tr>
                    <td>Amount</td>
                    <td style="color: #383d46;" align="left"><span style="width: 13px; font-weight: bold">
                            @include('reports.pdfs.taka_sign'){{number_format($amount,2)}}</span>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

                </tr>
                <tr>
                    <td>Purpose of Payment</td>
                    <td style="color: #383d46;">{{$description}}</td>
                </tr>
                <tr>
                    <td>Collection Time</td>
                    <td style="color: #383d46;">{{$created_at}}</td>
                </tr>
                <tr>
                    <td>Payment Option</td>
                    <td style="color: #383d46;">{{$method}}</td>
                </tr>
                <tr>
                    <td>Payment ID</td>
                    <td style="color: #383d46;font-weight: 500;">
                        @if(isset($payment_id)) #P-{{$payment_id}} @endif
                    </td>
                </tr>

            </table>
        </td>
    </tr>

    <tr>
        <td>
            <p class="note">
                Note: It can take upto 15 days to get refund in context to your application. Refund will be made through the same gateway you have used for payment.
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <footer style="padding-top: 10px">
                <div style="text-align: center;"><span style="position: absolute; left:300px; padding: 10px; color: #6F6F6F;">Powered by </span> <img alt="no-image" style="top:15px; position: absolute; left: 430px;" width="106.4px" height="27.59px" src="https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/employees_avatar/1586691331_arafat.png"/></div>
            </footer>
        </td>
    </tr>

    @endif

    </tbody>
</table>
</body>
</html>
