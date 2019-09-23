<!DOCTYPE html>
<html lang="en">
<head>
    <title> Invoice </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        .terms {
            font-size: 12px;
            border-top: 1px solid #e1e1e1;
            color: #4a4a4a;
        }

        body {
            font-family: DejaVu Sans, 'Shonar Bangla', 'Roboto', sans-serif;
        }
    </style>
</head>

<body>

<table align="center" style="max-width: 700px;margin: auto;min-width: 600px;font-family: sans-serif; font-size: smaller">
    <tbody>
    <tr>
        <td>
            <div style="width:100%;background-color:#F2F3F7;padding:37px; padding-top: 42px">
                <table class="" style="width: 100%;">
                    @if(isset($partner))
                        <tr>
                            <td width="120"><img style="max-width: 80px;padding-right: unset"
                                                 src="{{$partner['image']}}" alt=""></td>
                            <td colspan="3">
                                <div class="d-flex justify-content-between">
                                    <div style="text-align: left;padding: 10px 20px;padding-left: 0%">
                                        <span style="color: #383d46;letter-spacing: -0.05px;font-weight: 700;">{{ucfirst($partner['name'])}}</span><br>
                                    </div>
                                    <div style="text-align: right">
                                        <span style="color: #9b9b9b;">{{$partner['mobile']}}</span>
                                        <span style="padding-left: 10px; color: #C91F66"> <img style="width: 6%" src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/image/invoice/icon/call-material@3x.png" alt=""></span><br>
                                        <span style="color: #9b9b9b;">{{$partner['email']}}</span>
                                        <span style="padding-left: 10px ; color: #C91F66"><img style="width: 6%"  src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/image/invoice/icon/email-material@3x.png" alt=""></span><br>
                                        <span style="color: #9b9b9b;">{{$partner['address']}}</span>
                                        <span style="padding-left: 10px; color: #C91F66"><img style="width: 6%"  src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/image/invoice/icon/location-on-material@3x.png" alt=""></span>
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
        <td style="padding: 25px">
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
                <table class="table table-striped" style="width: 100%;color: #929292" cellspacing="5">
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
                            <td style="color: #383d46; padding: 1rem !important;"> {{ $order['service_name'] }} <br></td>
                            <td style="color: #383d46; padding: 1rem !important;">{{$order['id']}}</td>
                            <td style="color: #383d46; padding: 1rem !important;">{{$order['service_quantity']}}</td>
                            <td style="color: #383d46; padding: 1rem !important;">{{$order['service_unit_price']}}</td>
                            <td align="center" style="color: #383d46"><span
                                        style="width: 13px"></span> {{number_format(($order['total']), 2)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="float-right" style="min-width: 370px; font-weight: 600; padding-right: 14px">
                    <div class="d-flex justify-content-between bill-item">
                        <p>Total Service Price</p>
                        <p style="color: #383d46">{{number_format($original_price,2)}}</p>
                    </div>
                    <div class="d-flex justify-content-between bill-item">
                        <p>Discount</p>
                        <p style="color: #C91F66">{{number_format($discount,2)}}</p>
                    </div>
                    <div class="d-flex justify-content-between bill-item">
                        <p>Subtotal</p>
                        <p style="color: #383d46">{{number_format($subtotal,2)}}</p>
                    </div>
                    <div class="d-flex justify-content-between bill-item bill-item__total" style="background-color: #1b4280;color: white">
                        <p>Grand Total</p>
                        <p>{{number_format($subtotal,2)}}</p>
                    </div>
                </div>
            </td>
        </tr>
        <style>
            .bill-item {
                padding: 14px 16px;
            }

            .bill-item P {
                margin: 0;
            }
            .bill-item__total {
                padding: 16px;
                border-bottom-left-radius: 30px;
                border-top-left-radius: 30px;
            }
        </style>
    @endif
    <tr>
        <td style="padding-top: 38px">
            <p class="terms" style="border-top: none">
                <strong>*</strong>
                7 days service warranty. "No Tips" policy applicable.
            </p>
            <p class="terms" style="border-top: none">
                <strong>*</strong>
                This was created on a computer and is valid without the signature and seal.
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <hr>
            <p class="terms" style="border-top: none; padding-left: 172px">
              In association with: <span><img style="width: 15%"
                            src="https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/images/sheba_logo_blue.png" alt=""></span>
                <span style="padding-left: 10px; color: #C91F66"> <img style="width: 16px" src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/image/invoice/icon/call-material%403x.png" alt=""></span>
                <span style="padding-left: 5px">16516</span>
                <span style="padding-left: 10px; color: #C91F66"><img style="width: 16px" src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/image/invoice/icon/email-material@3x.png" alt=""></span>
                <span style="padding-left: 5px">info@sheba.xyz</span>
            </p>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>