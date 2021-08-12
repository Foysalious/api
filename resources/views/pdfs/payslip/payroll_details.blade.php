<!DOCTYPE html>

<html lang="en">

<head>
    <!-- start: Meta -->
    <title>Payslip</title>
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
        @if($pay_report_detail['employee_info']['company_logo'])
            <td class="text-left"><img src="{{ $pay_report_detail['employee_info']['company_logo'] }}" height="65"/></td>
        @endif
        @if($pay_report_detail['employee_info']['company_name'])
            <td class="text-right"><p class="company-name" style="font-family: 'Poppins', sans-serif">{{$pay_report_detail['employee_info']['company_name']}}</p></td>
        @endif
    </tr>
    <tr>
        <td><hr style=" color: #d1d7e6; width: 720px"></td>
    </tr>
</table>

<table style="border: none">
    <tr>
        <td>
            <p class="pdf-title">Salary Payslip</p>
        </td>
    </tr>
</table>

<table style="width: 100%; border: none; margin-top: -15px">
    <tr>
        <td style="width: 50%; border : none; vertical-align: top;">
            <table style="width: 100%; border : none">
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px">Salary of</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $pay_report_detail['salary_info']['salary_month'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px; width: 40%">Employee ID</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $pay_report_detail['employee_info']['employee_id'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px; width: 30%">Employee Name</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px">:</td>
                    <td style="padding-left: 10px;padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: bold; color: #000000; opacity: 1">{{ $pay_report_detail['employee_info']['name'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">Department</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $pay_report_detail['employee_info']['department'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">Designation</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px;  font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $pay_report_detail['employee_info']['designation'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px">Email</td>
                    <td style="vertical-align: top; font-family: 'Poppins', sans-serif; font-size: 12px; color: #000000; opacity: 0.8; padding-bottom: 5px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $pay_report_detail['employee_info']['email'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">Mobile Number</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8">{{ $pay_report_detail['employee_info']['mobile'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">Net Payable</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight:bold; color: #000000; opacity: 0.8">{{ number_format($pay_report_detail['salary_info']['net_payable'],2) }}</td>
                </tr>
                <tr style="width: 100%;">
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">In Words</td>
                    <td style="font-family: 'Poppins', sans-serif; padding-bottom: 5px; font-size: 12px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: normal; color: #000000; opacity: 0.8; width: 100%">{{ $pay_report_detail['salary_info']['net_payable_in_word'] }} Taka Only</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="tableHead" style="width: 100%; position: relative">
    <thead>
    <tr class="tableHeadRegular" style="background: #f8f8fb; width: 100%">
        <td style="width:80%;font-size: 12px; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 10px;border: solid 1px #d2d8e6;">
            Additions
        </td>
        <td style="width:20%; font-size: 12px; text-align: right; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 10px;border: solid 1px #d2d8e6;">
            Amount
        </td>
    </tr>
    </thead>

    <tbody>
    @foreach($pay_report_detail['addition']['breakdown'] as $key => $value)
        <tr style="width: 100%">
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:500px">
                {{ ucfirst($key) }}
            </td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:120px;text-align: right">
                {{ number_format($value,2) }}
            </td>
        </tr>
    @endforeach
    </tbody>

    <tfoot>
    <tr>
        <td style="font-size: 12px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
            Total
        </td>
        <td style="font-size: 12px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
            {{ number_format($pay_report_detail['addition']['total'],2) }}
        </td>
    </tr>

    </tfoot>

</table>
<br>
<table class="tableHead" style="width: 100%; position: relative">

    <thead>
    <tr class="tableHeadRegular" style="background: #f8f8fb; width: 100%">
        <td style="width:80%;font-size: 12px; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 10px;border: solid 1px #d2d8e6;">
            Deductions
        </td>
        <td style="width:20%; font-size: 12px; text-align: right; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 10px;border: solid 1px #d2d8e6;">
            Amount
        </td>
    </tr>
    </thead>

    <tbody>
    @foreach($pay_report_detail['deduction']['breakdown'] as $key => $value)
        <tr style="width: 100%">
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:500px">
                {{ ucfirst($key) }}
            </td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:120px;text-align: right">
                {{ number_format($value,2) }}
            </td>
        </tr>
    @endforeach
    </tbody>

    <tfoot>
    <tr>
        <td style="font-size: 12px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
            Total
        </td>
        <td style="font-size: 12px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
            {{ number_format($pay_report_detail['deduction']['total'],2) }}
        </td>
    </tr>

    </tfoot>

</table>
<br>
<table class="tableHead" style="width: 100%; position: relative;border: 0">

    <tbody>
    <tr style="width: 100%">
        <td style="font-size: 12px; opacity: 1; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px 10px;width:80%;text-align: right;">
            Gross Salary
        </td>
        <td style="font-size: 12px; opacity: 1; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:20%;text-align: right">
            {{ number_format($pay_report_detail['salary_info']['gross_salary'],2) }}
        </td>
    </tr>
    <tr style="width: 100%">
        <td style="font-size: 12px; opacity: 1; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px 10px;width:80%;text-align: right;">
            Total Addition
        </td>
        <td style="font-size: 12px; opacity: 1; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:20%;text-align: right">
            {{ number_format($pay_report_detail['addition']['total'],2) }}
        </td>
    </tr>
    <tr style="width: 100%">
        <td style="font-size: 12px; opacity: 1; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px 10px;width:80%;text-align: right;">
            Total Deduction
        </td>
        <td style="font-size: 12px; opacity: 1; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:20%;text-align: right">
            {{ number_format($pay_report_detail['deduction']['total'],2) }}
        </td>
    </tr>
    <tr style="width: 100%">
        <td style="font-size: 12px; opacity: 1; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px 10px;width:80%;text-align: right;">
            Net Payable
        </td>
        <td style="font-size: 12px; opacity: 1; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:20%;text-align: right">
            {{ number_format($pay_report_detail['salary_info']['net_payable'],2) }}
        </td>
    </tr>

    </tbody>

</table>
</body>

</html>
