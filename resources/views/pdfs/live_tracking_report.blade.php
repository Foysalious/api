<!DOCTYPE html>

<html lang="en">

<head>
    <!-- start: Meta -->
    <title>Live Tracking History</title>
    <meta name="description" content="">
    <meta name="author" content="Asad Ahmed">
    <meta name="keyword" content="">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        @media print {
            table { page-break-after: auto; page-break-inside: auto; }
            tr    { page-break-inside:avoid; page-break-after:auto }
            /*td    { page-break-inside:avoid; !*page-break-after:auto*! }*/
            /*thead { display: table-header-group}
            tfoot { display: table-footer-group}*/
        }
        ​@font-face {
            font-family: 'Poppins', sans-serif;
        }
        .text-center{
            text-align: center;
        }
        .text-right{
            text-align: right;
        }
        .text-left{
            text-align: left;
        }
        body {
            counter-reset: page;
        }
        /*
         !*new styles*!*/
        table, th {
            border: solid 1px #d2d8e6;
            border-collapse: collapse;
        }

        .table1th{
            /*font-family: 'Poppins', sans-serif;*/
            font-weight: normal;
            opacity: 0.8;
            font-size: 12px;
            text-align: left;
        }
        .tableHeadRegular{
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: bold;
            padding: 9px 20px;
            text-align: left;
            background-color: #fff8f8fb;
        }

        .tQuestion{
            font-size: 12px;
            font-weight: bold;
            font-family: 'Poppins', sans-serif;
            opacity: 0.8;
        }
        .tAnswer{
            font-size: 12px;
            opacity: 0.6;
            font-weight: normal;
            font-family: 'Poppins', sans-serif;
            padding-top: 5px;
        }
        .pageCounter:after {
            content: counter(page);
        }


        @page {
            margin-top: 20px;
        }
        .header{
            top: 0;
            left: 0;
            width: 100%;
            position: fixed;
            padding: 0;
            margin: 100px 0 0 0;
            background-color: #fff;
            border: none;
        }

        .company-name {
            margin: 0;
            padding-top: 27px;
            font-family: 'Poppins', sans-serif !important;
            opacity: 1;
            font-size: 18px;
            font-weight: 500;
            color: #000000;
        }

        .pdf-title {
            margin: -20px 0 0 0;
            padding: 0 0 25px 0;
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 300;
            color: #000000;
        }
        /* Footer */
        .footer {
            width: 100%;
            font-family: 'Poppins', sans-serif;
            position: fixed;
            left: 0;
            bottom: 4em;
            border: none;
        }

        .footer__row-title td {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            color: #000000;
            height: 80px;

        }

        .footer__row-info td {
            font-size: 12px;
            text-align: center;
            color: #000000;
        }

        ​/*new styles end*/
    </style>


</head>

<body style="margin-top: 20px; font-family: 'Poppins', sans-serif;">

<body style="margin: 50px 30px; font-family: 'Poppins', sans-serif; ">

<table class="header">
    <tr>
        @if($company['logo'])
            <td class="text-left"><img src="{{ $company['logo'] }}" height="50"/></td>
        @endif
        @if($company['name'])
            <td class="text-right"><p class="company-name" style="font-family: 'Poppins', sans-serif">{{$company['name']}}</p></td>
        @endif
    </tr>
    <tr>
        <td><hr style=" color: #d1d7e6; width: 720px"></td>
    </tr>
</table>

<table style="border: none">
    <tr>
        <td>
            <p class="pdf-title">Tracking History Report ({{$from_date}} To {{$to_date}})</p>
        </td>
    </tr>
</table>

<table style="width: 100%; border: none; margin-top: -15px">
    <tr>
        <td style="width: 50%; border : none; vertical-align: top;">
            <table style="width: 100%; border : none">
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px;">Employee ID</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $tracking_locations['employee']['employee_id'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px;">Employee Name</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px">:</td>
                    <td style="padding-left: 10px;padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: bold; color: #000000; opacity: 1">{{ $tracking_locations['employee']['employee_name'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">Department</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $tracking_locations['employee']['employee_department'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">Designation</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px;  font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $tracking_locations['employee']['employee_role'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px">Email</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $tracking_locations['employee']['employee_email'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">Mobile Number</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $tracking_locations['employee']['employee_mobile'] }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
@foreach($tracking_locations['timeline'] as $date => $location)
    <p style="width:20%;font-size: 15px; opacity: 1; font-weight: bold; font-family: 'Poppins', sans-serif;">Date: {{ \Carbon\Carbon::parse($date)->format('d-M-Y')}}</p>
    <table class="tableHead" style="width: 100%; position: relative">
    <thead>
    <tr class="tableHeadRegular" style="background: #f8f8fb; width: 100%">
        <td style="width:20%;font-size: 12px; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 10px;border: solid 1px #d2d8e6;">
            Serial No
        </td>
        <td style="width:30%; font-size: 12px; text-align: left; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 10px;border: solid 1px #d2d8e6;">
            Time
        </td>
        <td style="width:50%; font-size: 12px; text-align: left; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 10px;border: solid 1px #d2d8e6;">
            Location
        </td>
    </tr>
    </thead>

    <tbody>
    @php $key = 1 @endphp
    @foreach($location as $key => $timeline)
        <tr style="width: 100%">
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:15%">
                {{ ++$key }}
            </td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:25%;text-align: left">
                {{$timeline['time']}}
            </td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:60%;text-align: left">
                {{$timeline['address']}}
            </td>
        </tr>
    @endforeach
    </tbody>

</table>
@endforeach
</body>

</html>
