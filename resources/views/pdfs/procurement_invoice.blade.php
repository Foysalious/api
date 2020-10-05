<?php
$formatted_type = ucwords($procurement_info['type'])
?>
        <!DOCTYPE html>

<html lang="en">
<head>
    <!-- start: Meta -->
    <title>{{ $formatted_type }}</title>
    <meta name="description" content="">
    <meta name="author" content="Fazal Mahmud Niloy">
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

        /*invoice page*/
        .documentTitle {
            font-family: 'Lato';
            font-size: 20px;
            font-weight: bold;
            border: 0;
            width: 100%;
        }

        hr {
            border: solid 1px #d1d7e6;
        }

        .companyInfo {
            width: 100%;
            border: 0;
        }

        .addresses {
            width: 100%;
            border: 0;
            margin: 10px 0 0 12px;
        }

        .addressRow {
            font-weight: bold;
            height: 36px;
            vertical-align: top;
        }

        .itemsTable {
            width: 100%;
            margin-top: 30px;
            border: 0;
        }

        .itemsSpec {
            font-weight: bold;
            opacity: 0.8;
            font-family: Helvetica;
            font-size: 12px;
            background: #f8f8fb;
        }

        .itemsSpecLabel {
            border-top: solid 0.5px #bac0cc;
            border-bottom: solid 0.5px #bac0cc;
        }

        .border-left {
            border-left: solid 0.5px #bac0cc;
        }

        .padding-left {
            padding-left: 15px;
        }

        .border-right {
            border-right: solid 0.5px #bac0cc;
        }

        .itemsList {
            border-top: solid 0.5px #bac0cc;
            border-bottom: solid 0.5px #bac0cc;
            opacity: 0.8;
            font-family: Helvetica;
            font-size: 10px;
        }

        .total {
            font-weight: bold;
            opacity: 0.8;
            font-family: Helvetica;
            font-size: 12px;
            color: #333333;
        }

        /*invoice page end*/

        @font-face {
            font-family: Lato;
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

        @page {
            margin-top: 20px;
        }

        .header {
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
            bottom: 20px;
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

        .footerPrompt {
            opacity: 0.6;
            font-size: 10px;
            font-family: Rubik;
            height: 20px;
        }

        .logo {
            width: 40px;
            height: 40px;
            border-radius: 5px;
            border: solid 1px rgba(0, 0, 0, 0.05);
        }

        .companyInfoName {
            font-weight: bold;
            opacity: 0.8;
            font-family: Helvetica;
            font-size: 12px;
        }

        .companyInfoAddress {
            opacity: 0.8;
            font-family: Helvetica;
            font-size: 10px;
        }

        .companyInfoWorkOrderTitle {
            opacity: 0.6;
            font-family: Lato;
            font-size: 14px;
        }

        .companyInfoWorkOrderCode {
            opacity: 0.8;
            font-family: Lato;
            font-size: 14px;
            font-weight: bold;
        }

        .companyInfoBillInfo {
            font-family: Helvetica;
            font-size: 12px;
            color: #646465;
        }

        .companyInfoBillInfoDetails {
            font-family: Helvetica;
            font-size: 12px;
            color: #000000;
        }

        .addressesInfo {
            width: 100%;
            margin-top: 60px;
            border: solid 1px #d2d8e6;
            background-color: #f8f8fb;
        }

        .addressRowDetail {
            opacity: 0.8;
            font-family: Helvetica;
            font-size: 10px;
        }

        .billHeader {
            background-color: #f8f8fb;
        }

        .billHeader td {
            border: solid 1px #d2d8e6;
            opacity: 0.8;
            font-family: Helvetica;
            font-size: 12px;
            font-weight: bold;
        }

        .totalDetail {
            padding-top: 4px;
            padding-bottom: 4px;
        }
    </style>
    <meta name="keyword" content="">
</head>
<body style="margin-top: 55px; margin-bottom: 22px; font-family: Lato;">

{{--FOOTER START--}}
<table class="footer">
    <tr>
        <td colspan="2" class="footerPrompt">This is a digital version of W/O ,No signature is required here.</td>
    </tr>
    <tr>
        <td style="font-size: 10px; opacity: 0.8; font-weight: normal; width: 70px; padding-bottom: 0px">
            Powered by -
        </td>
        <td style="font-size: 10px;font-weight: normal;  padding-left: 10px; padding-bottom: 0px; padding-top: 5px">
            <img src="{{public_path("images/sheba@3x.png")}}" style="height: 16px">
        </td>
    </tr>
</table>
{{--FOOTER END--}}

<table class="documentTitle">
    <tr>
        <td>{{ $formatted_type }}</td>
    </tr>
    <tr>
        <td>
            <hr style="margin-top: 5px">
        </td>
    </tr>
</table>

<table class="companyInfo">
    <tr>
        <td>
            <table style="border: 0">
                <tr>
                    <td><img src="{{ $procurement_info['from']['logo'] }}" alt="logo" class="logo"></td>
                    <td class="padding-left"><span
                                class="companyInfoName">{{ $procurement_info['from']['name'] }}</span> <br>
                        <span class="companyInfoAddress">{{ $procurement_info['from']['address'] }}</span></td>
                </tr>
            </table>
        </td>
        <td>
            <table style="border: 0; margin-left: auto">
                <tr>
                    <td class="companyInfoWorkOrderTitle">{{ $formatted_type }} ID</td>
                    <td class="companyInfoWorkOrderTitle">:</td>
                    <td class="companyInfoWorkOrderCode">{{ $procurement_info['code'] }}</td>
                </tr>
                <tr>
                    <td class="companyInfoBillInfo">{{ $formatted_type }} Submitted Date</td>
                    <td class="companyInfoBillInfo">:</td>
                    <td class="companyInfoBillInfoDetails">{{ $procurement_info['submitted_date'] }}</td>
                </tr>
                @if ($procurement_info['type'] == 'bill')
                    <tr>
                        <td class="companyInfoBillInfo">{{ $formatted_type }} Payment Date</td>
                        <td class="companyInfoBillInfo">:</td>
                        <td class="companyInfoBillInfoDetails">{{ $procurement_info['payment_date'] }}</td>
                    </tr>
                    <tr>
                        <td class="companyInfoBillInfo">Payment Method</td>
                        <td class="companyInfoBillInfo">:</td>
                        <td class="companyInfoBillInfoDetails">{{ $procurement_info['payment_method'] }}</td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<div class="addressesInfo">
    <table class="addresses">
        <tr class="addressRow">
            <td style="margin-bottom: 16px">To Address</td>
            <td style="margin-bottom: 16px">From Address</td>
        </tr>
        <tr class="addressRowDetail">
            <td>
                <table cellpadding="5" style="border: 0">
                    <tr>
                        <td>Name</td>
                        <td>:</td>
                        <td>{{ $procurement_info['to']['name'] }}</td>
                    </tr>
                    <tr>
                        <td>Address</td>
                        <td>:</td>
                        <td>{{ $procurement_info['to']['address'] }}</td>
                    </tr>
                    <tr>
                        <td>Mobile</td>
                        <td>:</td>
                        <td>{{ $procurement_info['to']['mobile'] }}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table cellpadding="5" style="border: 0">
                    <tr>
                        <td>Name</td>
                        <td>:</td>
                        <td>{{ $procurement_info['from']['name'] }}</td>
                    </tr>
                    <tr>
                        <td>Address</td>
                        <td>:</td>
                        <td>{{ $procurement_info['from']['address'] }}</td>
                    </tr>
                    <tr>
                        <td>Mobile</td>
                        <td>:</td>
                        <td>{{ $procurement_info['from']['mobile'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<table class="itemsTable" cellpadding="10">
    <tr class="billHeader">
        <td class="billHeaderTD padding-left" colspan="6">BILL</td>
    </tr>
    <tr class="itemsSpec">
        <th class="itemsSpecLabel border-left padding-left">SL NO</th>
        <th class="itemsSpecLabel border-left padding-left">Item Name/Description</th>
        <th class="itemsSpecLabel border-left padding-left">Specification</th>
        <th class="itemsSpecLabel border-left padding-left">Unit</th>
        <th class="itemsSpecLabel border-left padding-left">Unit Price</th>
        <th class="itemsSpecLabel border-left padding-left">Price</th>
    </tr>

    @foreach($procurement_info['items'] as $key => $item)
        <tr class="itemsList">
            <td class="itemsList border-left padding-left">{{ ++$key }}</td>
            <td class="itemsList border-left padding-left">{{ $item['title'] }}</td>
            <td class="itemsList border-left padding-left">{{ $item['short_description'] }}</td>
            <td class="itemsList border-left padding-left">{{ $item['unit'] }}</td>
            <td class="itemsList border-left padding-left">{{ $item['unit_price'] }}</td>
            <td class="itemsList border-left padding-left border-right">{{ $item['total_price'] }}</td>
        </tr>
    @endforeach

    <tr class="total">
        <td colspan="4"></td>
        <td class="padding-left" style="padding-bottom: 4px">Sub total</td>
        <td class="padding-left" style="padding-bottom: 4px">
            <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign">
            {{ $procurement_info['sub_total'] }}
        </td>
    </tr>

    @if ($procurement_info['type'] == 'invoice')
        <tr class="total">
            <td colspan="4"></td>
            <td class="padding-left totalDetail">Amount to be paid</td>
            <td class="padding-left totalDetail">
                <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign">
                {{ $procurement_info['amount_to_be_paid'] }}
            </td>
        </tr>

        <tr class="total">
            <td colspan="4"></td>
            <td class="padding-left totalDetail">due</td>
            <td class="padding-left totalDetail">
                <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign">
                {{ $procurement_info['due_after_amount_to_be_paid'] }}
            </td>
        </tr>
    @endif

    @if ($procurement_info['type'] == 'bill')
        <tr class="total">
            <td colspan="4"></td>
            <td class="padding-left totalDetail">Paid</td>
            <td class="padding-left totalDetail">
                <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign">
                {{ $procurement_info['paid'] }}
            </td>
        </tr>

        <tr class="total">
            <td colspan="4"></td>
            <td class="padding-left totalDetail">due</td>
            <td class="padding-left totalDetail">
                <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign">
                {{ $procurement_info['due'] }}
            </td>
        </tr>
    @endif

    <tr>
        <td colspan="6">
            <hr style="margin: 2px 0 2px">
        </td>
    </tr>

    <tr class="total">
        <td colspan="4"></td>
        <td class="padding-left totalDetail">Grand Total</td>
        <td class="padding-left totalDetail">
            <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign">
            {{ $procurement_info['grand_total'] }}
        </td>
    </tr>
</table>

<script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->get_font("Lato", "regular");
        $size = 7;
        $y = $pdf->get_height() - 31;
        $x = $pdf->get_width() - $fontMetrics->get_text_width($text, $font, $size) + 49;
        $pdf->page_text($x, $y, $text, $font, $size);
    }

</script>
</body>
</html>
