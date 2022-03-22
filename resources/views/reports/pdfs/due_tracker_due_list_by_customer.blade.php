<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <title>Customer wise due list report</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="{{resource_path('assets/css/due_tracker_pdf.css')}}">
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
    <div class="due-list" style="margin-top: 20px">
        @include('reports.pdfs.partials._due_list_by_customer')
    </div>
    <table style="width: 100%;line-height: 1">
        <tr>
            <td style="width: 65%"></td>
            <td style="width: 35%">Balance: <span style="color:{{$balance["color"]}}">{{$balance["amount"]}} tk ( {{$balance["type"]}} )</span></td>
        </tr>
    </table>
</div>
</body>
</html>
