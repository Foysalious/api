<!DOCTYPE html>
<html lang="en">
<head>
    <title>Quotation details 1.1</title>
    <meta name="description" content="">
    <meta name="author" content="Dennis Ji">
    <meta name="keyword" content="">
    <style>
        @media print {
            table {
                page-break-after: auto;
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto
            }

        }

        @font-face {
            font-family: Lato;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        body {
            counter-reset: page;
        }

        table, th {
            border: solid 1px #d2d8e6;
            border-collapse: collapse;
        }

        .table1th {
            font-weight: normal;
            opacity: 0.8;
            font-size: 10px;
            text-align: left;
        }

        .tableHeadRegular {
            opacity: 0.8;
            font-family: Lato;
            font-size: 10px;
            font-weight: bold;
            padding: 9px 20px;
            text-align: left;
            background-color: #fff8f8fb;
        }

        .tQuestion {
            font-size: 10px;
            font-weight: bold;
            font-family: Lato;
            opacity: 0.8;
        }

        .tAnswer {
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

        /* Footer */
        .footer {
            width: 100%;
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

    </style>
</head>

<body style="margin-top: 20px; font-family: Lato;">

<table class="header">
    <tr>
        @if($leave_balance['logo'])
            <td class="text-left"><img src="{{ $leave_balance['logo'] }}" height="65"/></td>
        @endif
        @if($leave_balance['company'])
            <td class="text-right"><p class="company-name">{{$leave_balance['company']}}</p>
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
            <p class="pdf-title">Employee Leave Balance</p>
        </td>
    </tr>
</table>

<table style="width: 100%; border: none">
    <tr>
        <td style="width: 60%; border : none; vertical-align: top;">
            <table style="width: 100%; border : none">
                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">
                        Employee ID
                    </td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">
                        :
                    </td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">
                        {{$leave_balance['employee_id']}}
                    </td>
                </tr>

                <tr>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">
                        Employee Name
                    </td>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">
                        :
                    </td>
                    <td style="padding-left: 10px;  padding-bottom: 13px; font-family: Lato; font-size: 12px; font-weight: bold; color: #000000; opacity: 0.8">
                        {{$leave_balance['employee_name']}}
                    </td>
                </tr>

                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">
                        Department
                    </td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">
                        :
                    </td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">
                        {{$leave_balance['department']}}
                    </td>
                </tr>

                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">
                        Designation
                    </td>
                    <td style="font-family: Lato; padding-bottom: 13px;  font-size: 10px; color: #000000; opacity: 0.8">
                        :
                    </td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">
                        {{$leave_balance['designation']}}
                    </td>
                </tr>
                {{--<tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">
                        Mobile Number
                    </td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">
                        :
                    </td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">"01678016516"</td>
                </tr>--}}
            </table>
        </td>
        <td style="width: 40%">
            <table class="tableHead" style="width: 100%; table-spacing: 0px; margin-bottom: 75px">
                <thead>
                <tr>
                    <th class="tableHeadRegular" colspan="2" style="text-align: center; background-color: #f8f8fb">
                        Leave summary
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($leave_balance['leave_balance'] as $balance)
                    <tr>
                        <td style="font-size: 10px; font-weight: bold; width: 50%; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                            {{$balance['title']}}
                        </td>
                        <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                            {{$balance['used_leaves']}}/{{$balance['allowed_leaves']}}

                        </td>
                    </tr>
                @endforeach
                {{--<tr>
                    <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                        Sick
                    </td>
                    <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                        "15/14"
                    </td>

                </tr>
                <tr>
                    <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                        Casual
                    </td>
                    <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
                        "00/14"
                    </td>

                </tr>--}}
                </tbody>
            </table>
        </td>
    </tr>
</table>

<table style="width: 100%; border: none; padding-bottom: 20px">
    <tr>
        <td style="width: 60%; border : none; ">
            <table style="width: 100%; border : none">
                <tr>
                    <td style="opacity: 0.8; font-family: Lato; font-size: 14px; font-weight: bold; color: #000000;">
                        Leave Details
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- LEAVE BALANCE TABLE --}}
<table class="tableHead" style="width: 100%; margin-bottom: 20px; position: relative">
    <thead>
    <tr class="tableHeadRegular" style="background-color: #f8f8fb">
        <td style="font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px; border: solid 1px #d2d8e6; width:20%">
            Created at
        </td>
        <td style="font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px; border: solid 1px #d2d8e6;width: 20%">
            Leave Type
        </td>
        <td style="font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px; border: solid 1px #d2d8e6; width: 40%">
            Leave Days
        </td>
        <td style="font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px; border: solid 1px #d2d8e6;">
            Status
        </td>
    </tr>
    </thead>
    <tbody>
    @forelse($leave_balance['leaves'] as $leaves)
        <tr>
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px 10px; border: solid 1px #d2d8e6;">
                {{$leaves['date']}}
            </td>
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px; border: solid 1px #d2d8e6;">
                {{$leaves['leave_type']}}
            </td>
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px; border: solid 1px #d2d8e6;">
                {{$leaves['leave_days']}}
            </td>
            <td style="font-size: 10px; opacity: 0.8; padding: 5px 10px; font-family: Lato; border: solid 1px #d2d8e6;">
                {{$leaves['status']}}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4"
                style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px 10px;border: solid 1px #d2d8e6; text-align: center">
                No Leave Found
            </td>
        </tr>
    @endforelse
    </tbody>
    <tfoot></tfoot>
</table>
{{-- END LEAVE BALANCE TABLE --}}
<table class="footer"></table>

</body>
</html>
