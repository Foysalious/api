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

        .bill-item-container {
            display: table;
            position: relative;
            min-width: 370px;
            font-weight: 500;
            padding-right: 14px;
            width: 400px;
            float: right;
            page-break-inside: auto !important;
            page-break-after: always;
            alignment: right;
        }

        .bill-item {
            padding: 14px 16px;
            display: table-row;
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }


        .bill-item span {
            text-align: left;
            padding: 10px;
            position: relative;
            margin: 0;
            display: table-cell;
        }

        .bill-item span:last-child {
            text-align: right;
        }

        .bill-item__total {
            padding: 16px;
            width: 100%;
        }


        .bill-item__total span {
            background-color: #1b4280;
            color: white;
        }

        .bill-item__total span:first-child:before {
            position: absolute;
            left: -25.5px;
            width: 51px;
            height: 51px;
            right: auto;
            border-radius: 25.5px;
            content: '';
            z-index: -1;
            top: 0;
            background-color: #1b4280;
        }

        @media print {

            table {

                page-break-inside: auto !important;
                page-break-after: always;

            }

            table tbody tr {

                page-break-inside: avoid !important;

                page-break-after: auto !important;

            }

        }

        @page {
            margin: 0;
            padding: 0;
            border: none;

        }

    </style>
</head>

<body>
<table align="center" style="width: 100%;margin: auto;min-width: 600px;font-family: sans-serif; font-size: smaller">
    <tbody>
    <tr>
        <td>
            <div style="width:100%;background-color:#F2F3F7;padding:37px; padding-top: 42px">
                <table class="" style="width: 100%;">
                    @if(isset($partner))
                        <tr style="vertical-align: text-top">
                            <td class="text-left" width="80">
                                <div style="display: inline-block;text-align: left;">

                                    <img style="max-width: 80px;padding-right: unset;display: inline-block;"
                                         src="{{$partner['image']}}" alt="">
                                </div>
                            </td>
                            <td>
                                <div class="position-relative"
                                     style="white-space: normal;display: inline-block;text-align: left;width: 100%">
                                    <div style="text-align: left;float:left;padding: 10px 20px;">
                                        <span style="color: #383d46;letter-spacing: -0.05px;font-weight: 700;">{{ucfirst($partner['name'])}}</span><br>
                                    </div>
                                </div>
                            </td>
                            <td colspan="3" style="text-align: right">
                                <div style="width: 100%;text-align: right">
                                    <div style="text-align: right; float: right">
                                        <span style="color: #7c808f;">{{$partner['mobile']}}</span>
                                        <span style="padding-left: 10px; color: #C91F66"> <img style="width: 14px"
                                                                                               src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/image/invoice/icon/call-material@3x.png"
                                                                                               alt=""></span><br>
                                        <span style="color: #7c808f;">{{$partner['email']}}</span>
                                        <span style="padding-left: 10px ; color: #C91F66"><img style="width: 14px"
                                                                                               src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/image/invoice/icon/email-material@3x.png"
                                                                                               alt=""></span><br>
                                        <span style="color: #7c808f;">{{$partner['address']}}</span>
                                        <span style="padding-left: 10px; color: #C91F66"><img style="width: 14px"
                                                                                              src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/image/invoice/icon/location-on-material@3x.png"
                                                                                              alt=""></span>
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
                            <div style="display: block">
                                <div style="float: left">
                                    <span style="color: #7c808f;">Bill to:</span>
                                    <span style="color: #7c808f;letter-spacing: -0.05px;font-weight: 700;">{{ucfirst($customer['name'])}}</span><br>
                                    <div style="padding-left: 45px">
                                        <span style="color: #7c808f">{{$customer['mobile']}}</span>
                                    </div>
                                </div>
                                <div style="float: right">
                                    <span style="color: #7c808f;">Bill Number:</span>
                                    <span style="color: #C91F66;letter-spacing: -0.05px;font-weight: 700;">{{$subscription_code}}</span><br>
                                    <span style="color: #7c808f;">Date Of Bill:</span>
                                    <span style="color: #7c808f">{{$bill_pay_date}}</span>
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    </tbody>
</table>
@if(isset($orders))
    <table class="table table-striped data-table"
           style="width: 100%;color: #929292;min-width: 600px;font-family: sans-serif; font-size: x-small"
           cellspacing="5">
        <thead style="background-color: #7B83A5; color: white;">
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
    <div style="width: 100%;display: block;text-align: right;padding: 20px 20px 0;position: relative;">
        <div class="bill-item-container"
             style="">
            <div class="bill-item">
                <span style="">Total Service Price</span>
                <span style="color: #383d46;text-align: right">{{number_format($original_price,2)}}</span>
            </div>
            <div class="bill-item">
                <span style="">Discount</span>
                <span style="color: #C91F66;text-align: right">{{number_format($discount,2)}}</span>
            </div>
            <div class="bill-item">
                <span style="">Subtotal</span>
                <span style="color: #383d46;text-align: right">{{number_format($subtotal,2)}}</span>
            </div>
            <div class=" bill-item bill-item__total"
                 style="">
                <span style="">Grand Total</span>
                <span style="text-align: right">{{number_format($subtotal,2)}}</span>
            </div>
        </div>
    </div>

@endif
    <div style="padding: 40px;">
        <table style="width: 100%;">
            <tbody>
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
                    <div class="terms" style="padding-left: 150px;">
                        <table>
                            <tr>
                                <td><span> </span>
                                    <span>In association with:</span>
                                </td>
                                <td>
                                    <span style="padding-left: 20px; color: #C91F66"></span>
                                    <span>
                                        <img style="width: 90px; padding-top: 16px"
                                             src="https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/images/sheba_logo_blue.png"
                                             alt=""></span>
                                </td>
                                <td>
                                    <span style="padding-left: 20px; color: #C91F66">
                                        <img
                                                style="width: 16px; padding-top: 15px"
                                                src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/image/invoice/icon/call-material%403x.png"
                                                alt=""></span>
                                    <span style="padding-left: 10px">16516</span>
                                </td>
                                <td>
                                    <span style="padding-left: 20px; color: #C91F66">
                                        <img style="width: 16px;padding-top: 15px"
                                             src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/image/invoice/icon/email-material@3x.png"
                                             alt=""></span>
                                    <span style="padding-left: 10px">info@sheba.xyz</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
