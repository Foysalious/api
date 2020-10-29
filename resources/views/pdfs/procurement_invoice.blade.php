<?php $formatted_type = ucwords($procurement_info['type']) ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $formatted_type }}</title>
    <meta name="description" content="">
    <meta name="author" content="Asad Ahmed">
    <meta name="keyword" content="">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        @page {
            margin-top: 10px;
            margin-left: 0px;
            margin-right: 0px;
        }

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

        .addresses {
            width: 100%;
            border: 0;
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
            background: #f8f8fb;
            opacity: 0.8;
            font-family: Helvetica;
            font-size: 12px;
            font-weight: bold;
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
        }

        /*invoice page end*/
        @font-face {
            font-family: Lato;
        }

        body {
            counter-reset: page;
            margin: 25px;
        }

        table, th {
            border: solid 1px #d2d8e6;
            border-collapse: collapse;
        }

        .table1th {
            /*font-family: Lato;*/
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
            /*position: fixed;*/
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

        /*new styles end*/
        .logo {
            width: 40px;
            height: 40px;
            border-radius: 5px;
            border: solid 1px rgba(0, 0, 0, 0.05);
        }

        .companyInfo {
            width: 100%;
            border: 0;
        }

        .addressesInfo {
            width: 100%;
            margin-top: 80px;
            border: solid 1px #d2d8e6;
            background-color: #f8f8fb;
        }

        .addressRowDetail {
            opacity: 0.8;
            font-family: Helvetica;
            font-size: 10px;
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
            font-size: 16px;
        }

        .companyInfoWorkOrderCode {
            opacity: 0.8;
            font-family: Lato;
            font-size: 16px;
            font-weight: bold;
        }

        .rules {
            border: 0;
            width: 100%;
        }

        .mode-list {
            border: 0;
            margin-left: 30px;
        }

        .myheader {
            border: 0;
            width: 80%;
            /*position: fixed;
            top: 0;*/
            margin: 0 auto 10px;
        }

        .myheader h5 {
            font-family: Arial, sans-serif;
            font-size: 22px;
            color: #1a214e;
            margin: 0 0 0 50px;
        }

        .mode-list tr td {
            padding: 3px;
        }

        .myfooter {
            width: 100%;
            border: 0;
            background: #070550;
            min-height: 155px;
            position: fixed;
            bottom: 6.4em;
            margin-left: 0;
            margin-right: -60px;
        }

        .vertical-line {
            width: 0px;
            z-index: 10011;
            border-right: thin solid red;
            position: absolute;
            height: 100%;
            left: 20px;
        }

        .getintouch {
            border: 0;
        }

        .getintouch .touch-title, .touch-contact {
            color: #fff;
            font-size: 16px;
            font-weight: bold;
        }

        .footerlogo {
            width: 25%;
        }

        tr td address {
            padding-left: 10px;
            color: #fff;
            font-size: 12px;
        }

        .social {
            border: 0;
            color: #fff;
            font-size: 12px;
        }

        .social h4 {
            font-size: 16px;
            margin-bottom: 0;
            margin-top: 10px;
            font-weight: bold;
        }

        tr.social-link td {
            height: 20px;
        }

        tr.social-link span {
            font-size: 10px;
            border: 1px solid #fff;
            border-radius: 50%;
            padding: 3px;
        }

        a {
            text-decoration: none;
            color: #fff
        }
    </style>
</head>

<body style="margin-top: 10px; margin-bottom: 0px; font-family: Lato;">
    <table class="myheader" width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
        <tr style="border-collapse:collapse">
            <td align="right" style="padding-right: 35px;" width="50%">
                <a href="https://business.sheba.xyz" target="_blank">
                    <img width="300px" src="https://business.sheba.xyz/assets/img/statics/sBusiness.png" alt="Sheba Business Logo" title="sBusiness">
                </a>
            </td>
            <td style="border: 2px solid #807ea0">
                <span></span>
            </td>
            <td align="left" width="50%">
                <h5>Your Business<br>Assistant</h5>
            </td>
        </tr>
    </table>

    <table class="documentTitle">
        <tr align="center">
            <td>
                <span style="text-decoration: underline;">{{ $formatted_type }}</span>
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

    <table class="itemsTable" cellpadding="5">
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
                <td class="padding-left totalDetail">Paid</td>
                <td class="padding-left totalDetail">
                    <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign">
                    {{ $procurement_info['paid'] }}
                </td>
            </tr>
            @if ($procurement_info['is_for_payment_request'])
            <tr class="total">
                <td colspan="4"></td>
                <td class="padding-left totalDetail">Amount to be paid</td>
                <td class="padding-left totalDetail">
                    <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign"> {{ $procurement_info['amount_to_be_paid'] }}
                </td>
            </tr>
            @endif
            <tr class="total">
                <td colspan="4"></td>
                <td class="padding-left totalDetail">Due</td>
                <td class="padding-left totalDetail">
                    <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign"> {{ $procurement_info['due_after_amount_to_be_paid'] }}
                </td>
            </tr>
        @endif

        @if ($procurement_info['type'] == 'bill')
            <tr class="total">
                <td colspan="4"></td>
                <td class="padding-left totalDetail">Paid</td>
                <td class="padding-left totalDetail">
                    <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign"> {{ $procurement_info['paid'] }}
                </td>
            </tr>
            <tr class="total">
                <td colspan="4"></td>
                <td class="padding-left totalDetail">due</td>
                <td class="padding-left totalDetail">
                    <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign"> {{ $procurement_info['due'] }}
                </td>
            </tr>
        @endif

        <tr>
            <td colspan="6"><hr style="margin: 2px 0 2px"></td>
        </tr>

        <tr class="total">
            <td colspan="4"></td>
            <td class="padding-left totalDetail">Grand Total</td>
            <td class="padding-left totalDetail">
                <img style="width: 12px; height: 13px;" src="{{ $procurement_info['tk_sign'] }}" alt="tk_sign"> {{ $procurement_info['grand_total'] }}
            </td>
        </tr>
    </table>

    <table class="rules" cellpadding="5">
        <tr class="amount">
            <td colspan="2" align=""><b>In Word:</b> {{ $procurement_info['total_amount_in_word'] }}</td>
        </tr>
        <tr>
            <td colspan="2">
                <b>Terms & Conditions:</b>
                <p style="margin-top: -12px">{!! $procurement_info['terms_and_conditions'] !!}</p>
            </td>
        </tr>
    </table>

    <table class="myfooter">
        <tr>
            <td class="footerlogo" align="center">
                <img src="http://aarplanet.com/sbusiness_Logo.png" width="100">
            </td>
            <td style="width: 40%;padding-left: 0px;padding-right: 0px;">
                <table class="getintouch">
                    <tr class="touch-title"><td colspan="2">Get in Touch</td></tr>
                    <tr class="touch-title"><td colspan="2"><hr></td></tr>
                    <tr>
                        <td class="touch-contact">16516<br>b2b@sheba.xyz</td>
                        <td>
                            <address>
                                House #63 (1st floor)<br>
                                Road #04, Block-C<br>
                                Banani, Dhaka 1213<br>
                            </address>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="">
                <table class="social">
                    <tr>
                        <td><h4>Follow Us on Social Platform</h4></td>
                    </tr>
                    <tr>
                        <td>
                            <hr>
                        </td>
                    </tr>
                    <tr class="social-link">
                        <td><a href="bit.ly/sBusinessFB"><img src="http://aarplanet.com/facebook.png"
                                                              style="width: 16px;vertical-align: top;border: 1px solid #fff;border-radius: 50%;">&nbsp;bit.ly/sBusinessFB</a>
                        </td>
                    </tr>
                    <tr class="social-link">
                        <td><a href="bit.ly/sBusinessLinkedIn"><img src="http://aarplanet.com/linkedin.png"
                                                                    style="width: 16px;vertical-align: top;border: 1px solid #fff;border-radius: 50%;">&nbsp;bit.ly/sBusinessLinkedIn</a>
                        </td>
                    </tr>
                    <tr class="social-link">
                        <td><a href="bit.ly/sBusinessInsta"><img src="http://aarplanet.com/instagram.png"
                                                                 style="width: 16px;vertical-align: top;border: 1px solid #fff;border-radius: 50%;">&nbsp;bit.ly/sBusinessInsta</a>
                        </td>
                    </tr>
                    <tr class="social-link">
                        <td><a href="bit.ly/sBusinessYouTube"><img src="http://aarplanet.com/youtube.png"
                                                                   style="width: 16px;vertical-align: top;border: 1px solid #fff;border-radius: 50%;">&nbsp;bit.ly/sBusinessYouTube</a>
                        </td>
                    </tr>
                </table>
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
