<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <title>Quotation details 1.1</title>
    <meta name="description" content="">
    <meta name="author" content="Dennis Ji">
    <meta name="keyword" content="">
    <style>
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

        .table1th{/*
            font-family: Lato;*/
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
            background-color: #f8f8fb;
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

        .footer {
            width: 100%;
            margin-bottom: 22px;
            position: fixed;
            bottom: 0;
            left: 0;
            border: solid 0px #d2d8e6;
        }

        ​/*new styles end*/
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

{{--1st table--}}
<table  class="tableHeadRegular" style="width: 100%;  margin-bottom: 20px; background-color: #f8f8fb; padding: 0px" >
    <tr style="border: 0px">
        <td class="table1th" style="padding: 20px 20px 4px 20px; font-family: Lato">
            ID
        </td>
        <td class="table1th" style="padding: 20px 20px 4px 20px">
            Procurement Title
        </td>
        <td class="table1th"style="padding: 20px 0px 4px 0px">
            Created On
        </td>
    </tr>
    <tr>
        <td style="font-size: 10px; font-weight: bold; font-family: Lato;padding: 4px 20px 20px 20px; width: 30%">
            #{{$bid_details['procurement_id']}}
            <span style="padding-left: 10px; display: inline-block; margin-top: 3px">
                <span style="background-color: #0c99f7; color: white; border: 0px;font-size: 8px;padding: 0 5px; display: inline-block; font-family: Rubik">
                {{ucwords($bid_details['status'])}}
                </span>
            </span>
        </td>
        <td style="font-size: 10px; font-weight: bold; font-family: Lato;padding: 4px 20px 20px 20px; width: 53%">
            {{$bid_details['title']}}
        </td>
        <td style="font-size: 10px; font-weight: bold; font-family: Lato;padding: 4px 0px 20px 0px">
            {{$bid_details['start_date']}}
        </td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 0px 20px 20px 20px;">
            <table style="border: solid 0px #d2d8e6; width: 100%">
                <tr>
                    <td rowspan="2" style="width: 50%" >
                        <table style="border: solid 0px #d2d8e6;">
                            <tr>
                                <td rowspan="2">
                                    <img src="{{$bid_details['vendor']['logo']}}" alt="sheba logo" style="width: 40px; height: 40px; border-radius: 5px;border: solid 1px rgba(0, 0, 0, 0.05);">
                                </td>
                                <td style="padding: 6px 8px 8px 10px">{{$bid_details['vendor']['name']}}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 10px">
                                    <span><img src="{{public_path("images/star.png")}}" style="height: 8px"></span>
                                    <span style="color: #ff8219; font-family: Rubik;padding-left: 3px;padding-right: 5px">{{$bid_details['vendor']['rating']}}</span>
                                    <span style="font-size: 8px;opacity: 0.4; font-family: Rubik">({{$bid_details['vendor']['total_rating']}} ratings)</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 34%" class="table1th">
                        Quotations
                    </td>
                    <td class="table1th" style="padding-left: 8px;">
                        Delivery Date
                    </td>
                </tr>
                <tr>
                    <td style="">
                        {{$bid_details['price']}}
                    </td>
                    <td style="padding-left: 8px;">
                        {{$bid_details['end_date']}}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{--2nd table--}}
<table  class="tableHead" style="width: 100%;  margin-bottom: 20px">
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
<table  class="tableHead" style="width: 100%; table-spacing: 0px; margin-bottom: 20px">
    <tr>
        <th class="tableHeadRegular"  colspan="5">
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

{{--4th table--}}
<table  class="tableHead" style="width: 100%;  margin-bottom: 20px;border: solid 1px #d2d8e6;border-collapse: collapse;page-break-after: always;">
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

{{--2nd PAGE--}}

{{--1st table--}}
<table  class="tableHeadRegular" style="width: 100%;  margin-bottom: 20px; background-color: #f8f8fb; padding: 0px" >
    <tr style="border: 0px">
        <td class="table1th" style="padding: 20px 20px 4px 20px">
            ID
        </td>
        <td class="table1th" style="padding: 20px 20px 4px 20px">
            Procurement Title
        </td>
        <td class="table1th"style="padding: 20px 0px 4px 0px">
            Created On
        </td>
    </tr>
    <tr>
        <td style="font-size: 10px; font-weight: bold; font-family: Lato;padding: 4px 20px 20px 20px; width: 30%">
            {{$bid_details['procurement_id']}}
            <span style="padding-left: 10px">
                <span style="background-color: #0c99f7; color: white; border: 0px;font-size: 8px;padding: 2px 5px">
                {{$bid_details['status']}}
                </span>
            </span>
        </td>
        <td style="font-size: 10px; font-weight: bold; font-family: Lato;padding: 4px 20px 20px 20px; width: 53%">
            {{$bid_details['title']}}
        </td>
        <td style="font-size: 10px; font-weight: bold; font-family: Lato;padding: 4px 0px 20px 0px">
            {{$bid_details['start_date']}}
        </td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 0px 20px 20px 20px;">
            <table style="border: solid 0px #d2d8e6; width: 100%">
                <tr>
                    <td rowspan="2" style="width: 50%" >
                        <table style="border: solid 0px #d2d8e6;">
                            <tr>
                                <td rowspan="2">
                                    <img src="{{$bid_details['vendor']['logo']}}" alt="sheba logo" style="width: 40px; height: 40px; border-radius: 5px;border: solid 1px rgba(0, 0, 0, 0.05);">
                                </td>
                                <td style="padding: 6px 8px 8px 10px">{{$bid_details['vendor']['name']}}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 10px">
                                    <span><img src="{{public_path("images/star.png")}}" style="height: 8px"></span>
                                    <span style="color: #ff8219; font-family: Rubik;padding-left: 3px;padding-right: 5px">{{$bid_details['vendor']['rating']}}</span>
                                    <span style="font-size: 8px;opacity: 0.4; font-family: Rubik">({{$bid_details['vendor']['total_rating']}})</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 34%" class="table1th">
                        Quotations
                    </td>
                    <td class="table1th" style="padding-left: 8px;">
                        Delivery Date
                    </td>
                </tr>
                <tr>
                    <td style="">
                        {{$bid_details['price']}}
                    </td>
                    <td style="padding-left: 8px;">
                        {{$bid_details['end_date']}}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{{--2nd table--}}
<table  class="tableHead" style="width: 100%;  margin-bottom: 20px;border: solid 1px #d2d8e6;border-collapse: collapse">
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
