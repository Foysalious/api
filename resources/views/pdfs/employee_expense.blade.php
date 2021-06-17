<!DOCTYPE html>

<html lang="en">

<head>
    <!-- start: Meta -->
    <title>Quotation details 1.1</title>
    <meta name="description" content="">
    <meta name="author" content="Dennis Ji">
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
            padding: 0 0 20px 0;
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 300;
            color: #000000;
        }

        .expense-table {

        }

        .expense-table__header {

        }

        .expense-table__header td {

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


        /*.footer-row:after {*/
        /*    content: "";*/
        /*    display: table;*/
        /*    clear: both;*/
        /*}*/

        /*.footer-row-header,*/
        /*.footer-row-info {*/
        /*    float: left;*/
        /*    width: 25%;*/
        /*    padding: 10px;*/
        /*}*/

        /*.footer-row-header span {*/
        /*    font-size: 12px;*/
        /*    font-weight: bold;*/
        /*    text-align: center;*/
        /*    color: #000000;*/
        /*}*/

        /*.footer-row-info span {*/
        /*    font-size: 10px;*/
        /*    text-align: center;*/
        /*    color: #000000;*/
        /*}*/




        ​/*new styles end*/
    </style>


</head>

<body style="margin-top: 20px; font-family: Lato;">

{{--<body style="margin: 50px 30px; font-family: Lato; ">--}}



<table class="header">
    <tr>
        @if($data['logo'])
            <td class="text-left"><img src="{{ $data['logo'] }}" height="65"/></td>
        @endif
        @if($data['company'])
            <td class="text-right"><p class="company-name">{{$data['company']}}</p>
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
            <p class="pdf-title">Employee Expense</p>
        </td>
    </tr>
</table>

<table style="width: 100%; border: none">
    <tr>
        <td style="width: 60%; border : none; vertical-align: top;">
            <table style="width: 100%; border : none">
                <tr>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">Employee Name</td>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: Lato; font-size: 12px; font-weight: bold; color: #000000; opacity: 0.8">{{ $data['employee_name'] }}</td>
                </tr>

                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Designation</td>
                    <td style="font-family: Lato; padding-bottom: 13px;  font-size: 10px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">{{ $data['designation'] }}</td>
                </tr>

                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Department</td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">{{ $data['department'] }}</td>
                </tr>


                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Mobile Number</td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">{{ $data['employee_mobile'] }}</td>
                </tr>
            </table>
        </td>

        <td style="width: 40%">
            <table class="tableHead" style="width: 100%; table-spacing: 0px; margin-bottom: 75px">

                <thead>
                <tr >
                    <th class="tableHeadRegular" colspan="2" style="text-align: center; background-color: #f8f8fb">
                        Office Use Only
                    </th>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <td style="font-size: 10px; font-weight: bold; width: 50%; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                        Conveyance
                    </td>
                    <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                        Holiday
                    </td>
                    <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">

                    </td>

                </tr>
                <tr>
                    <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                        Food
                    </td>
                    <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">

                    </td>

                </tr>
                <tr>
                    <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                        Expense Claim
                    </td>
                    <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">

                    </td>

                </tr>
                <tr>
                    <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                        Total
                    </td>
                    <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">

                    </td>
                </tr>
                </tbody>

            </table>
        </td>
    </tr>
</table>

<table style="width: 100%; border: none; padding-bottom: 40px">
    <tr>
        <td style="width: 60%; border : none; ">
            <table style="width: 100%; border : none">
                <tr>
                    <td style="font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px; width: 36.5%;">
                        Amount Requested
                    </td>
                    <td style="font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px; width: 2%">
                        :
                    </td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: Lato; font-size: 14px; font-weight: bold; color: #000000; opacity: 0.8">
                        {{ $data['total_amount'] }}
                    </td>
                </tr>

                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Month</td>
                    <td style="font-family: Lato; padding-bottom: 13px;  font-size: 10px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">
                        {{ $data['month_name'] }}
                    </td>
                </tr>
            </table>
        </td>

        <td style="border : none; padding-bottom: 35px">
            <table style="width: 100%; border : none">
                <tr >
                    <td style="font-family: Lato; vertical-align: top; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">In Words</td>
                    <td style="font-family: Lato; vertical-align: top; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">
                        {{ $data['total_amount_in_words'] }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Expense Table --}}
<table class="tableHead" style="width: 100%; margin-bottom: 20px; position: relative">

    <thead>
        <tr class="tableHeadRegular" style="background-color: #f8f8fb">
        <td style="font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;width:20%">
            Date
        </td>
        <td style="font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;width: 20%">
            Type
        </td>
        <td style="font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;;width: 40%">
            Remarks
        </td>
        <td style="font-size: 10px; text-align: right; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;">
            Amount
        </td>
    </tr>
    </thead>

    {{--    @foreach($bid_details['price_quotation'] as $price_quotation)--}}
    <tbody>
    @foreach($data['expenses']->toArray() as $expense)
    <tr>
        <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px 10px;border: solid 1px #d2d8e6;">
            {{ $expense['created_at'] }}
        </td>
        <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
            {{ $expense['type'] }}
        </td>
        <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
            {{ $expense['remarks'] }}
        </td>
        <td style="font-size: 10px; opacity: 0.8; padding: 5px 10px; font-family: Lato;border: solid 1px #d2d8e6; text-align: right; ">
            {{ formatTakaToDecimal($expense['amount'], true) }}
        </td>
    </tr>
    @endforeach


    </tbody>
    {{--    @endforeach--}}


    <tfoot>
    <tr>
        <td></td>
        <td></td>
        <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: Lato; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;">
            Sum
        </td>
        <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: Lato; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;">
            {{ $data['total_amount'] }}
        </td>
    </tr>

    </tfoot>


</table>

<table class="footer" >
    <tr class="footer__row-title">
        <td>Requested by</td>
        <td>Checked by</td>
        <td>Recommended by</td>
        <td>Approved by</td>
    </tr>
    <tr class="footer__row-info">
        <td>Applicant</td>
        <td>Supervisor</td>
        <td>Head of Department</td>
        <td>HR Department</td>
    </tr>
</table>

{{--<div class="footer">--}}

{{--    <div class="footer-row" style="height: 60px">--}}
{{--        <div class="footer-row-header">--}}
{{--            <span>Requested by</span>--}}
{{--        </div>--}}
{{--        <div class="footer-row-header">--}}
{{--            <span>Checked by</span>--}}
{{--        </div>--}}
{{--        <div class="footer-row-header">--}}
{{--            <span>Recommended by</span>--}}
{{--        </div>--}}
{{--        <div class="footer-row-header">--}}
{{--            <span>Approved by</span>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <div class="footer-row" >--}}
{{--        <div class="footer-row-info">--}}
{{--            <span>Applicant</span>--}}
{{--        </div>--}}
{{--        <div class="footer-row-info">--}}
{{--            <span>Supervisor</span>--}}
{{--        </div>--}}
{{--        <div class="footer-row-info">--}}
{{--            <span>Head of Department</span>--}}
{{--        </div>--}}
{{--        <div class="footer-row-info">--}}
{{--            <span>HR Department</span>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--</div>--}}





</body>

</html>
