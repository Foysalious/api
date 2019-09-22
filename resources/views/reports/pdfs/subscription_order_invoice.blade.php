<!DOCTYPE html>
<html lang="en">
<head>
    <title> Invoice </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
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
    <tr>
        <td>
            <div style="width:100%;background-color:#F2F3F7;padding:14px; padding-top: 42px">
                <table class="" style="width: 100%;">
                    @if(isset($partner))
                        <tr>
                            <td width="120"><img style="max-width: 80px;padding-right: unset" src="{{$partner['image']}}" alt=""></td>
                            <td colspan="3">
                                <div class="d-flex justify-content-between">
                                    <div style="text-align: left;padding: 10px 20px;padding-left: 0%">
                                        <span style="color: #383d46;letter-spacing: -0.05px;font-weight: 700;">{{ucfirst($partner['name'])}}</span><br>
                                    </div>
                                    <div style="text-align: right">
                                        <span style="color: #9b9b9b;">{{$partner['mobile']}}</span>
                                        <span style="padding-left: 10px"> <i class="fa fa-phone" aria-hidden="true"></i></span><br>
                                        <span style="color: #9b9b9b;">{{$partner['email']}}</span>
                                        <span style="padding-left: 10px"><i class="fa fa-envelope"
                                                                            aria-hidden="true"></i></span><br>
                                        <span style="color: #9b9b9b;">{{$partner['address']}}</span>
                                        <span style="padding-left: 10px"><i class="fa fa-map-marker"
                                                                            aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <table class="table table-striped" style="width: 100%">
                <tr class="table-light">
                    <td style="border-left: 4px; border-top: none">
                        @if(isset($customer))
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span style="color: #B0BEC5;">Bill to:</span>
                                    <span style="color: #383d46;letter-spacing: -0.05px;font-weight: 700;">{{ucfirst($customer['name'])}}</span><br>
                                    <span style="padding-left:45px;color: #9b9b9b">{{$customer['mobile']}}</span>
                                </div>
                                <div>
                                    <span style="color: #B0BEC5;">Bill Number:</span>
                                    <span style="color: #383d46;letter-spacing: -0.05px;font-weight: 700;">{{$subscription_code}}</span><br>
                                    <span style="color: #B0BEC5;">Date Of Bill:</span>
                                    <span style="color: #9b9b9b">{{$bill_pay_date}}</span>
                                </div>
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
                <table class="table table-striped" style="width: 100%;color: #929292"
                       cellspacing="5">
                    <thead style="background-color: #7B83A5; color: white">
                    <tr>
                        <th>Service Name</th>
                        <th>Order Code</th>
                        <th>QTY</th>
                        <th>Unit Price</th>
                        <th align="right">Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td style="color: #383d46"> {{ $order['service_name'] }} <br></td>
                            <td style="color: #383d46">{{$order['id']}}</td>
                            <td style="color: #383d46">{{$order['service_quantity']}}</td>
                            <td style="color: #383d46">{{$order['service_unit_price']}}</td>
                            <td align="right" style="color: #383d46"><span
                                        style="width: 13px"></span> {{number_format(($order['total']), 2)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <table class="float-right">
                    <tbody style="font-weight: bold">
                    <tr>
                        <td colspan="4">Total Service Price</td>
                        <td align="right" style="color: #383d46"><span
                                    style="width: 13px ; padding-left: 60px"></span> {{number_format($original_price,2)}}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">Discount</td>
                        <td align="right" style="color: #383d46"><span
                                    style="width: 13px"></span> {{number_format($discount,2)}}</td>
                    </tr>
                    <tr>
                        <td colspan="4">Subtotal</td>
                        <td align="right" style="color: #383d46"><span
                                    style="width: 13px"></span> {{number_format($subtotal,2)}}</td>
                    </tr>
                    <tr style="background-color: #1b4280;color: white">
                        <td colspan="4">Grand Total</td>
                        <td align="right"><span
                                    style="width: 13px"></span> {{number_format($subtotal,2)}}</td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    @endif
    <hr>
    <tr>
        <td>
            <p class="terms" style="border-top: none">
                <strong>Terms and Note:</strong>
                If needed, it can take a maximum of 15 days to get your refund. You will only get return
                by the way you pay.
            </p>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>