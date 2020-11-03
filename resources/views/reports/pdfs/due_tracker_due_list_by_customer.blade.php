<!DOCTYPE html>
<html lang="en" >
<head>
    <title>Customer wise due list report</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style type="text/css">
        @font-face {
            font-family: 'Kalpurush';
            font-style: normal;
            font-weight: normal;
            src: url(http://example.com/fonts/firefly.ttf) format('truetype');
        }
        @import url('https://fonts.maateen.me/kalpurush/font.css');

        .page-break {
            page-break-after: always;
        }

        .break-before {
            page-break-before: auto;
        }
        body {
            font-family: 'Kalpurush', Arial, sans-serif !important;
            color: #4a4a4a;
            font-style: normal;
            font-weight: normal;
        }

        .heading {
            text-align: center;
            margin-top: 20px;
        }

        .heading h2 {
            font-size: 1.5rem;
        }

        .heading .sub-heading {
            font-size: 1rem;
            font-family: 'Mukti','Roboto',sans-serif;
        }

        .heading .sub-text {
            font-size: .9rem;
            font-family: 'Mukti','Roboto',sans-serif;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            font-family: 'Mukti','Roboto',sans-serif;
        }

        .table td {
            text-align: center;
            padding: 0px 10px;
            line-height: 1;
        }

        .table-head {
            background-color: #ededed;
            font-weight: normal;
        }

        .table-head th {
            font-family: 'Mukti','Roboto',sans-serif;
            font-weight: normal;
            padding: 0px 10px;
            text-align: center;
        }
        @page {
            margin: 40px;
            padding: 2cm;
            footer: page-footer;
        }

        @media print {

            .table {
                page-break-inside: auto !important;
                page-break-before: avoid !important;
            }

            .table tbody tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }

        }

        /** Define the footer rules **/
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            text-align: center;
            line-height: 1.5cm;
        }



        #pageCounter:before {
            content: "Page " counter(page) ;
        }

        #counter {
            position: fixed;
            bottom: 0cm;
            left: auto;
            right: 30px;
            height: 2cm;
            text-align: center;
            line-height: 1.5cm;
            width: 120px;
        }
        .timeline {
            text-align: center;
            padding: 10px;
            background-color: #F5F5F5;
            margin: 5px 0;
        }
    </style>
</head>
<body align="center">
<?php $today = \Carbon\Carbon::today()->format('h:i A');?>
<div>
    <table style="width: 100%">
        <tr>
            <td style="width: 50%;text-align: left">
                <img style="width: auto;height: 50px" src="{{ $partner["avatar"] }}" alt="log">
                <p><i class="fa fa-phone" aria-hidden="true"></i>{{$partner["mobile"]}}</p>
            </td>
            <td style="width: 50%;text-align: right">
                <img src={{ config('constants.smanager_logo') }} alt="">
                <p style="color: white"><i class="fa fa-phone" aria-hidden="true"></i>{{$partner["mobile"]}}</p>
            </td>

        </tr>
    </table>
    @if($start_date && $end_date)
    <table style="width: 100%">
        <tr>
            <td>
                <div class="timeline">
                   <p style="margin-bottom: 0;">Transaction deadline: {{ date('d-m-Y', strtotime($start_date)) }} -- {{ date('d-m-Y', strtotime($end_date))}}</p>
                </div>
            </td>
        </tr>
    </table>
    @endif
    <table style="width: 100%;line-height: 1">
        <tr>
            <td style="width: 65%"> Customer name: {{ $customer["name"]}}</td>
            <td style="width: 35%">Number of Transactions: {{$other_info["total_transactions"]}}</td>
        </tr>
    </table>
    <table style="width: 100%;line-height: 1">
        <tr>
            <td style="width: 65%">Total credits: <span style="color: #219653">{{$other_info["total_credit"]}} tk</span></td>
            <td style="width: 35%">Total debits: <span style="color: #DC1E1E">{{$other_info["total_debit"]}} tk</span></td>
        </tr>
    </table>
    <table style="width: 100%;line-height: 1">
        <tr>
            <td style="width: 65%">Date: {{ date("d-m-Y, h:i:s a") }}</td>
            <td style="width: 35%">Balance: <span style="color:{{$balance["color"]}}">{{$balance["amount"]}} tk ( {{$balance["type"]}} )</span></td>
        </tr>
    </table>
    <table class="table table-bordered">
        <thead>
        <tr class="table-head">
            <th>No</th>
            <th>Date</th>
            <th>Description</th>
            <th>Credit</th>
            <th>Debit</th>
        </tr>
        </thead>
        <tbody>
            @foreach($list as $key=>$item)
            <tr>
                <td style="width: 9%">{{++$key}}</td>
                <td style="width: 15%">{{date('d-m-Y', strtotime($item['created_at'])) }}</td>

                @if($item['source_type'] === 'PosOrder')
                    <td style="width: 50%">Purchase, Order Id #{{$item['partner_wise_order_id']}}</td>
                @elseif($item['head'] === 'Due Tracker')
                    @if($item['note'])
                        @if(strlen($item['note']) > 120)
                            <td style="width: 50%">{{substr($item['note'], 0, 120)}}... </td>
                        @else
                            <td style="width: 50%"> {{$item['note']}} </td>
                        @endif
                    @else
                        <td style="width: 50%"> -- </td>
                    @endif
                @else
                    <td style="width: 50%">{{$item['head']}}</td>
                @endif
                @if($item['type'] === 'deposit')
                    <td style="color: #219653;width: 13%">0</td>
                    <td style="color: #DC1E1E;width: 13%">{{$item['amount'] }}</td>
                    @else
                    <td style="color: #219653;width: 13%">{{$item['amount'] }}</td>
                    <td style="color: #DC1E1E;width: 13%">0</td>
                @endif
            </tr>
        @endforeach
            <tr>
                <td style="text-align: right" colspan="3">Total</td>
                <td style="color: #219653">{{$other_info["total_credit"]}}</td>
                <td style="color: #DC1E1E">{{$other_info["total_debit"]}}</td>
            </tr>
        </tbody>
    </table>
    <table style="width: 100%;line-height: 1">
        <tr>
            <td style="width: 65%"></td>
            <td style="width: 35%">Balance: <span style="color:{{$balance["color"]}}">{{$balance["amount"]}} tk ( {{$balance["type"]}} )</span></td>
        </tr>
    </table>
</div>
</body>
</html>
