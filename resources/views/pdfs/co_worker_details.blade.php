<!DOCTYPE html>

<html lang="en">

<head>
    <!-- start: Meta -->
    <title>Co Worker Details</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keyword" content="">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        @media print {
            table { page-break-after: auto; page-break-inside: auto; }
            tr    { page-break-inside:avoid; page-break-after:auto }
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

        .tableHeadRegular{
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: bold;
            padding: 9px 20px;
            text-align: left;
            background-color: #fff8f8fb;
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
            font-family: 'Poppins', sans-serif;
            opacity: 0.8;
            font-size: 18px;
            font-weight: 500;
            color: #000000;
        }

        .pdf-title {
            margin: -20px 0 0 0;
            padding: 0 0 25px 0;
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
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

{{--<body style="margin: 50px 30px; font-family: 'Poppins', sans-serif; ">--}}

<table class="header">
    <tr>
        @if($employee['pdf_info']['company_logo'])
            <td class="text-left"><img src="{{ $employee['pdf_info']['company_logo'] }}" height="65"/></td>
        @endif
        @if($employee['pdf_info']['company_name'])
            <td class="text-right"><p class="company-name">{{$employee['pdf_info']['company_name']}}</p>
            </td>
        @endif
    </tr>
    <tr>
        <td><hr style=" color: #d1d7e6; width: 720px"></td>
    </tr>
</table>

<table style="border: none">
    <tr>
        <td>
            <p class="pdf-title">Employee Detail</p>
        </td>
    </tr>
</table>

<table style="width: 100%; border: none; margin-top: -15px">
    <tr>
        <td style="width: 50%; border : none; vertical-align: top;">
            <table style="width: 50%; border : none">
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 13px">Employee Name</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $employee['basic_info']['profile']['name'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 13px">Email</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $employee['basic_info']['profile']['email'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 13px">Department</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                    <td style="padding-left: 10px; padding-top: 7px; padding-bottom: 13px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: bold; color: #000000; opacity: 1">{{ $employee['basic_info']['department'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 13px; font-size: 12px; color: #000000; opacity: 0.8">Designation</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 13px; font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $employee['basic_info']['designation'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 13px; font-size: 12px; color: #000000; opacity: 0.8">Manager</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 13px;  font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $employee['basic_info']['manager_detail']['name'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 13px">Date of joining</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $employee['basic_info']['department'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 13px; font-size: 12px; color: #000000; opacity: 0.8">Employee Type</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 13px; font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ ucfirst($employee['official_info']['employee_type']) }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 13px; font-size: 12px; color: #000000; opacity: 0.8">Employee ID</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 13px; font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $employee['official_info']['employee_id'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 13px; font-size: 12px; color: #000000; opacity: 0.8">Grade</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 13px; font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $employee['official_info']['grade'] }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="tableHead" style="width: 100%; margin-bottom: 20px; position: relative">

    <thead>
    <tr class="tableHeadRegular" style="background: #f8f8fb; width: 100%">
        <td style="width:80%;font-size: 12px; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 10px;border: solid 1px #d2d8e6;">
            Personal
        </td>
    </tr>
    </thead>

    <tbody>
    <tr style="width: 100%">
        <td style="width: 20%">Gender</td>
        <td style="width: 20%">:</td>
        <td style="width: 20%">{{ $employee['personal_info']['gender'] }}</td>
    </tr>
    <tr style="width: 100%">
        <td style="width: 20%">Phone</td>
        <td style="width: 20%">:</td>
        <td style="width: 20%">{{ $employee['personal_info']['mobile'] }}</td>
    </tr>
    </tbody>

</table>

</body>

</html>
