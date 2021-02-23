<!DOCTYPE html>

<html lang="en">

<head>
    <!-- start: Meta -->
    <title>Payrooll Details</title>
    <meta name="description" content="">
    <meta name="author" content="Asad Ahmed">
    <meta name="keyword" content="">
    <style>
        @media print {
            table { page-break-after: auto; page-break-inside: auto; }
            tr    { page-break-inside:avoid; page-break-after:auto }
            /*td    { page-break-inside:avoid; !*page-break-after:auto*! }*/
            /*thead { display: table-header-group}
            tfoot { display: table-footer-group}*/
        }
        ​@font-face {
            font-family: Lato;
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
            /*font-family: Lato;*/
            font-weight: normal;
            opacity: 0.8;
            font-size: 10px;
            text-align: left;
        }
        .tableHeadRegular{
            opacity: 0.8;
            font-family: Lato;
            font-size: 10px;
            font-weight: bold;
            padding: 9px 20px;
            text-align: left;
            background-color: #fff8f8fb;
        }

        .tQuestion{
            font-size: 10px;
            font-weight: bold;
            font-family: Lato;
            opacity: 0.8;
        }
        .tAnswer{
            font-size: 10px;
            opacity: 0.6;
            font-weight: normal;
            font-family: Lato;
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
            position: fixed;
            width: 100%;
            margin-top: 110px;
            background-color: #f8f8fb;
        }
        /* Footer */
        .footer {
            width: 100%;
            font-family: Lato;
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
            font-size: 10px;
            text-align: center;
            color: #000000;
        }

        ​/*new styles end*/
    </style>


</head>

<body style="margin-top: 20px; font-family: Lato;">

{{--<body style="margin: 50px 30px; font-family: Lato; ">--}}

<table  class="tableHeadRegular header" style="width: 100%;  margin-bottom: 20px; padding: 0px; background-color: #fff;border: none " >
    <tr>
        <td style=""><p style="padding-left:17px; margin-top: -3px; margin-bottom: 0px; opacity: 0.8; font-family: Lato; font-size: 20px; color: #000000;">Employee Salary</p></td>
        <td style="text-align: right">
            <p style="margin-top: -3px; margin-bottom: 0px; opacity: 0.8; font-family: Lato; font-size: 24px; font-weight: 300; color: #000000;">{{$pay_report_detail['employee_info']['company_name']}}</p>
        </td>
    </tr>
    <tr>
        <td><hr style=" color: #d1d7e6; width: 720px"></td>
    </tr>
</table>

<table style="width: 100%; border: none; margin-top: -15px">
    <tr>
        <td style="width: 50%; border : none; vertical-align: top;">
            <table style="width: 50%; border : none">
                <tr>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">Salary of</td>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: Lato; font-size: 12px; font-weight: bold; color: #000000; opacity: 0.8">{{ $pay_report_detail['salary_info']['salary_month'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">Employee ID</td>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: Lato; font-size: 12px; font-weight: bold; color: #000000; opacity: 0.8">{{ $pay_report_detail['employee_info']['employee_id'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">Employee Name</td>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: Lato; font-size: 13px; font-weight: bold; color: #000000; opacity: 1">{{ $pay_report_detail['employee_info']['name'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Department</td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">{{ $pay_report_detail['employee_info']['department'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Designation</td>
                    <td style="font-family: Lato; padding-bottom: 13px;  font-size: 10px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">{{ $pay_report_detail['employee_info']['designation'] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">Email</td>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: Lato; font-size: 12px; font-weight: bold; color: #000000; opacity: 0.8">{{ $pay_report_detail['employee_info']['email'] }}</td>
                </tr>
                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Mobile Number</td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">{{ $pay_report_detail['employee_info']['mobile'] }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br>

<table style="width: 100%; border: none; padding-bottom: 40px">
    <tr>
        <td style="width: 100%; border : none; ">
            <table style="width: 67%; border : none">
                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8; width: 39.2%;">
                        Net Payable
                    </td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 1; width: 3%">
                        :
                    </td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 11px; font-weight: bold; color: #000000; opacity: 1;">
                        {{ $pay_report_detail['salary_info']['net_payable'] }}
                    </td>
                </tr>
                <tr >
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">In Words</td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8;width:100%;">
                        {{ $pay_report_detail['salary_info']['net_payable_in_word'] }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="tableHead" style="width: 100%; margin-bottom: 20px; position: relative">

    <thead>
    <tr class="tableHeadRegular" style="background: #f8f8fb; width: 100%">
        <td style="width:80%;font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;">
            Addition
        </td>
        <td style="width:20%; font-size: 10px; text-align: right; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;">
            Amount
        </td>
    </tr>
    </thead>

    <tbody>
    @foreach($pay_report_detail['addition']['breakdown'] as $key => $value)
        <tr style="width: 100%">
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px 10px;border: solid 1px #d2d8e6;width:80%">
                {{ ucfirst($key) }}
            </td>
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;width:20%;text-align: right">
                {{ $value }}
            </td>
        </tr>
    @endforeach
    </tbody>

    <tfoot>
    <tr>
        <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: Lato; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
            Total
        </td>
        <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: Lato; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
            {{ $pay_report_detail['addition']['total'] }}
        </td>
    </tr>

    </tfoot>

</table>
<br>
<table class="tableHead" style="width: 100%; margin-bottom: 20px; position: relative">

    <thead>
    <tr class="tableHeadRegular" style="background: #f8f8fb; width: 100%">
        <td style="width:80%;font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;">
            Deduction
        </td>
        <td style="width:20%; font-size: 10px; text-align: right; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;">
            Amount
        </td>
    </tr>
    </thead>

    <tbody>
    @foreach($pay_report_detail['deduction']['breakdown'] as $key => $value)
        <tr style="width: 100%">
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px 10px;border: solid 1px #d2d8e6;width:80%">
                {{ ucfirst($key) }}
            </td>
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;width:20%;text-align: right">
                {{ $value }}
            </td>
        </tr>
    @endforeach
    </tbody>

    <tfoot>
    <tr>
        <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: Lato; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
            Total
        </td>
        <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: Lato; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
            {{ $pay_report_detail['deduction']['total'] }}
        </td>
    </tr>

    </tfoot>

</table>
<br>
<table class="tableHead" style="width: 100%; margin-bottom: 20px; position: relative;border: 0">

    <tbody>
    <tr style="width: 100%">
        <td style="font-size: 10px; opacity: 1; font-weight: normal; font-family: Lato; padding: 5px 10px;width:80%;text-align: right;">
            Gross Salary
        </td>
        <td style="font-size: 10px; opacity: 1; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;width:20%;text-align: right">
            {{ $pay_report_detail['salary_info']['gross_salary'] }}
        </td>
    </tr>
    <tr style="width: 100%">
        <td style="font-size: 10px; opacity: 1; font-weight: normal; font-family: Lato; padding: 5px 10px;width:80%;text-align: right;">
            Addition
        </td>
        <td style="font-size: 10px; opacity: 1; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;width:20%;text-align: right">
            {{ $pay_report_detail['addition']['total'] }}
        </td>
    </tr>
    <tr style="width: 100%">
        <td style="font-size: 10px; opacity: 1; font-weight: normal; font-family: Lato; padding: 5px 10px;width:80%;text-align: right;">
            Deduction
        </td>
        <td style="font-size: 10px; opacity: 1; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;width:20%;text-align: right">
            {{ $pay_report_detail['deduction']['total'] }}
        </td>
    </tr>
    <tr style="width: 100%">
        <td style="font-size: 10px; opacity: 1; font-weight: normal; font-family: Lato; padding: 5px 10px;width:80%;text-align: right;">
            Net Payable
        </td>
        <td style="font-size: 10px; opacity: 1; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;width:20%;text-align: right">
            {{ $pay_report_detail['salary_info']['net_payable'] }}
        </td>
    </tr>

    </tbody>

</table>
</body>

</html>
