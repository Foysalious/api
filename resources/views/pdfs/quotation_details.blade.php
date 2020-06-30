<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <title>Quotation details 1.1</title>
    <meta name="description" content="">
    <meta name="author" content="Sheba">
    <meta name="keyword" content="">
    <style>
        @media print {
            table {
                page-break-after: auto
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

        /*
         !*new styles*!*/
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
            background-color: #f8f8fb;
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

        .footer {
            width: 100%;
            margin-bottom: 22px;
            position: fixed;
            bottom: 0;
            left: 0;
            border: solid 0px #d2d8e6;
        }

        ​ /*new styles end*/
        .documentTitle {
            font-family: 'Lato';
            font-size: 20px;
            font-weight: bold;
            border: 0;
            width: 100%;
        }

        .companyInfo {
            width: 100%;
            border: 0;
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

        /*new styles end*/
        @page {
            margin: 150px 50px 50px 50px;
        }

        .header {
            position: fixed;
            top: -100px;
            right: 0px;
            left: 0px;
            height: 100px;
            /* background-color: #6AB0F9;*/
        }

        .tenderTitle {
            width: 100%;
            padding: 18px;
            margin-bottom: 20px;
            border: solid 1px #d2d8e6;
            background-color: #f8f8fb;
        }

        .tenderTitle div {
            opacity: 0.8;
            font-family: Helvetica;
            font-size: 10px;
        }

        .generalInfoHeader {
            font-weight: normal;
            font-size: 10px;
            text-align: left;
            padding: 20px 20px 4px 20px;
            opacity: 0.6
        }

        .generalInfoData {
            font-size: 10px;
            font-weight: bold;
            padding: 4px 20px 20px 20px;
        }

    </style>
</head>
<body style="margin-top: 20px; font-family: Lato">

{{--footer--}}
<table class="footer" style="width: 100%; font-family: Lato">
    <tr>
        <td style="font-size: 10px; opacity: 0.8; font-weight: normal; width: 70px; padding-bottom: 0px">
            Powered by -
        </td>
        <td style="font-size: 10px;font-weight: normal;  padding-left: 10px; padding-bottom: 0px; padding-top: 5px">
            <img src="{{public_path("images/sheba@3x.png")}}" style="height: 16px">
        </td>
    </tr>
</table>

{{--header--}}
<div class="header">
    <table class="documentTitle" style="width: 100%; border: 0;">
        <tr>
            <td>Quotation</td>
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
                        <td><img src="{{ $bid_details['vendor']['logo'] }}" alt="logo" class="logo"></td>
                        <td>
                            <span class="companyInfoName">{{ $bid_details['vendor']['name'] }}</span> <br>
                            <span><img src="{{public_path("images/star.png")}}" style="height: 8px"></span>
                            <span class="companyInfoAddress">{{$bid_details['vendor']['rating']}}</span>
                            <span class="companyInfoAddress">({{$bid_details['vendor']['total_rating']}} ratings)</span>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <table style="border: 0; margin-left: auto">
                    <tr>
                        <td class="companyInfoWorkOrderTitle">ID</td>
                        <td class="companyInfoWorkOrderTitle">:</td>
                        <td class="companyInfoWorkOrderCode">#{{ $bid_details['id'] }}</td>
                    </tr>
                    <tr>
                        <td class="companyInfoBillInfo">Created on</td>
                        <td class="companyInfoBillInfo">:</td>
                        <td class="companyInfoBillInfoDetails">{{ $bid_details['created_at'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="tenderTitle">
    <div>Procurement Title</div>
    <div style="font-weight: bold; margin-top: 4px">{{ $bid_details['title'] }}</div>
</div>

{{--2nd table jhamela--}}
<table class="" style="width: 100%; margin-bottom: 20px">
    <tr>
        <th class="tableHeadRegular" colspan="3"> General Information</th>
    </tr>
    <tr style="border: 0px">
        <td class="generalInfoHeader">Labels</td>
        <td class="generalInfoHeader">Delivery Date</td>
        <td class="generalInfoHeader">Payment Method</td>
    </tr>
    <tr>
        <td class="generalInfoData">
            {{ implode($bid_details['labels'], ', ') }}
        </td>
        <td class="generalInfoData">
            {{ $bid_details['start_date']}} - {{$bid_details['end_date'] }}
        </td>
        <td class="generalInfoData">
            {{ $bid_details['payment_options'] }}
        </td>
    </tr>
</table>

{{--2nd table--}}
<table class="tableHead" style="width: 100%;  margin-bottom: 20px">
    <tr>
        <th class="tableHeadRegular">
            Proposal
        </th>
    </tr>
    <tr>
        <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 20px;">
            {{$bid_details['proposal']}}
        </td>
    </tr>
</table>

{{--3rd table--}}

@if(count($bid_details['price_quotation']))
    <table class="tableHead" style="width: 100%; table-spacing: 0px; margin-bottom: 20px">
        <tr>
            <th class="tableHeadRegular" colspan="5">
                Price Quotation
            </th>
        </tr>
        <tr>
            <td style="font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;width:8%">
                SL NO
            </td>
            <td style="font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;width: 30%">
                Item Name / Description
            </td>
            <td style="font-size: 10px; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;;width: 40%">
                Specification
            </td>
            <td style="font-size: 10px; text-align: right; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;">
                Unit
            </td>
            <td style="font-size: 10px; text-align: right; opacity: 0.8; font-weight: bold; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;">
                Price
            </td>
        </tr>
        @foreach($bid_details['price_quotation'] as $price_quotation)
            <tr>
                <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;">
                    {{$price_quotation['id']}}
                </td>
                <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;">
                    {{$price_quotation['title']}}
                </td>
                <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6;">
                    {{$price_quotation['short_description']}}
                </td>
                <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6; text-align: right; ">
                    {{json_decode($price_quotation['variables'],true)['unit']}}
                </td>
                <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 10px;border: solid 1px #d2d8e6; text-align: right; ">
                    {{$price_quotation['result']}}
                </td>
            </tr>
        @endforeach
    </table>
@endif

{{--4th table--}}
@if(count($bid_details['technical_evaluation']))
    <table class="tableHead"
           style="width: 100%;  margin-bottom: 20px;border: solid 1px #d2d8e6;border-collapse: collapse;">
        <tr>
            <th class="tableHeadRegular">
                Technical Evaluation
            </th>
        </tr>
        <tr>
            <td style="padding: 7px 20px 20px 20px">
        @foreach($bid_details['technical_evaluation'] as $technical_evaluation)
            <tr>
                <td style="padding: 0px 20px 20px 20px">
                    <table style="border: solid 0px #d2d8e6;">
                        <tr>
                            <td class="tQuestion">
                                {{ $technical_evaluation['title'] }}
                            </td>
                        </tr>
                        <tr>
                            <td class="tAnswer">
                                {{ $technical_evaluation['result'] }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @endforeach
            </td>
            </tr>
    </table>
@endif
{{--2nd table--}}
@if(count($bid_details['company_evaluation']))
    <table class="tableHead"
           style="width: 100%;  margin-bottom: 20px;border: solid 1px #d2d8e6;border-collapse: collapse">
        <tr>
            <th class="tableHeadRegular">
                Company Evaluation
            </th>
        </tr>
        <tr>
            <td style="padding: 7px 20px 20px 20px">
        @foreach($bid_details['company_evaluation'] as $company_evaluation)
            <tr>
                <td style="padding: 0px 20px 20px 20px">
                    <table style="border: solid 0px #d2d8e6;">
                        <tr>
                            <td class="tQuestion">
                                {{ $company_evaluation['title'] }}
                            </td>
                        </tr>
                        <tr>
                            <td class="tAnswer">
                                {{ $company_evaluation['result'] }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @endforeach
            </td>
            </tr>
    </table>
@endif

<script type="text/php">
        if (isset($pdf))
        {
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
