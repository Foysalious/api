<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en" >
<head>
    <title>Due list report</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="{{resource_path('assets/css/due_tracker_pdf.css')}}">
</head>
<body align="center">
<?php $today = \Carbon\Carbon::today()->format('h:i A');?>
<div>
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
    </div>
    @if($start_date && $end_date)
        <div>
            <table style="width: 100%">
                <tr>
                    <td>
                        <div class="timeline">
                            <p style="margin-bottom: 0;">Transaction deadline: {{ date('d-m-Y', strtotime($start_date)) }} -- {{ date('d-m-Y', strtotime($end_date))}}</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @endif
    <div>
        <table style="width: 100%;line-height: 1">

            <tr>
                <td style="width: 70%">Date: {{ date("d-m-Y, h:i:s a") }}</td>
                <td style="width: 30%" >Deposit: <span style="color: #219653">{{$stats["deposit"]}} tk</span></td>
            </tr>
        </table>
    </div>
    <div>
        <table style="width: 100%;line-height: 1">
            <tr>
                <td style="width: 70%"> Number of Transactions: {{ $total_transactions }}</td>
                <td style="width: 30%">Due: <span style="color: red">{{$stats["due"]}} tk</span> </td>
            </tr>
        </table>
    </div>
    <div>
        <table style="width: 100%;line-height: 1">
            <tr>
                <td style="width: 70%">Final Balance: <span>{{$stats["deposit"] - $stats["due"]}} tk</span></td>
            </tr>
        </table>
    </div>
    <div class="due-list" style="margin-top: 20px">
        @include('reports.pdfs.partials._due_list')
    </div>
</div>
</body>
</html>
