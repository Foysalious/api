<?php

    if(!function_exists('showRadioOrCheckbox')) {
        function showRadioOrCheckbox($title, $options, $image) {
            $template = '
            <table  style="border: solid 0px #d2d8e6; width: 100%">
                <tr>
                    <td class="tQuestion"> ' . $title . '</td>
                </tr>
            ';

            foreach ($options as $option) {
                $template .= '
                <tr>
                    <td class="tAnswer" style="padding-top: 10px">
                        <span>
                            <img src="' . $image . '" alt="check box" class="radioBtn">
                        </span>
                        <span style="display: inline">' . $option . '</span>
                    </td>
                </tr>
                ';
            }
            $template .= "</table>";
            return $template;
        }
    }

    if(!function_exists('showCheckBoxes')) {
        function showCheckBoxes($title, $options) {
        $image = public_path('images/check-box-outline-blank.png');
        return showRadioOrCheckbox($title, $options, $image);
    }
    }

    if(!function_exists('showRadio')) {
        function showRadio($title, $options) {
        $image = public_path('images/radio-button-unchecked.png');
        return showRadioOrCheckbox($title, $options, $image);
    }
    }

    if(!function_exists('showLines')) {
        function showLines($title, $placeholder, $no_of_lines) {
        $template = '
        <table style="border: solid 0px #d2d8e6;">
            <tr>
                <td class="tQuestion">' . $title . '</td>
            </tr>
            <tr>
                <td class="tAnswer" style="opacity: 0.4; padding-top: 10px">'. $placeholder .'</td>
            </tr>
        ';

        for ($i=1; $i<=$no_of_lines; $i++) {
            $template .= '<tr>
                <td>
                    <hr class="border">
                </td>
            </tr>';
        }
        $template .= "</table>";
        return $template;
    }
    }

    if(!function_exists('showShortAnswer')) {
        function showShortAnswer($title) {
        return showLines($title, 'Short answer', 1);
    }
    }

    if(!function_exists('showNumber')) {
        function showNumber($title) {
        return showLines($title, 'Number', 1);
    }
    }

    if(!function_exists('showLongAnswer')) {
        function showLongAnswer($title) {
        return showLines($title, 'Long Answer', 2);
    }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <title>Procurement Details</title>
    <style>
        @media print {
            table { page-break-after:auto }
            tr    { page-break-inside:avoid; page-break-after:auto }
            /*td    { page-break-inside:avoid; !*page-break-after:auto*! }*/
            /*thead { display: table-header-group}
            tfoot { display: table-footer-group}*/
        }
        body {
            counter-reset: page;
            font-family: Lato;
        }

        table {
            border: solid 1px #d2d8e6;
            border-collapse: collapse;
            counter-reset: tableCount;

        }
        th {
            border-collapse: collapse;
            counter-reset: tableCount;
        }
        tfoot th{
            border-bottom: solid 1px #d2d8e6;
            border-collapse: collapse;
        }
        thead th{
            border-top: solid 1px #d2d8e6;
            border-collapse: collapse;
        }

        .outerTable {
            width: 100%;
            margin-bottom: 20px;
            border: solid 1px #d2d8e6;
            border-collapse: collapse
        }

        .table1th{
            
            font-weight: normal;
            font-size: 10px;
            text-align: left;
        }
        .tableHeadRegular{
            opacity: 0.8;
            font-size: 10px;
            font-weight: bold;
            padding: 9px 20px;
            text-align: left;
            background-color: #f8f8fb;
            border: solid 1px #d2d8e6;
        }
        .border{
            width: 535px;
            border: 1px dashed;
            color: #cccccc
        }
        .radioBtn{
            height: 16px;
            display: inline-block;
            vertical-align: text-bottom;
            padding-right: 5px;
        }
        .tQuestion{
            font-size: 10px;
            font-weight: bold;
            opacity: 0.8;
        }
        .tAnswer{
            font-size: 10px;
            opacity: 0.6;
            font-weight: normal;
            padding-top: 5px;
        }

        .pageCounter:after {
            content: "Page " counter(page) " of " counter(pages);
        }

        .footer {
            width: 100%;
            margin-bottom: 22px;
            position: fixed;
            bottom: 0;
            left: 0;
            border: solid 0px #d2d8e6;
        }
        .header{
            top: 0;
            left: 0;
            position: fixed;
            width: 100%;
            margin-top: -80px;
            background-color: #f8f8fb;
        }
        .boldText{
            font-size: 10px;
            font-weight: bold;
            opacity: 0.8
        }

        .status{
            background-color: #0c99f7;
            color: white;
            border: 0px;
            font-size: 8px;
            padding: 0 5px;
            display: inline-block;
            font-family: Rubik;
        }

        .counterCell:before {
            content: counter(tableCount);
            counter-increment: tableCount;
        }

        @page {
            margin-top: 140px;
        }

        ​/*new styles end*/
    </style>
</head>

<body style="margin-top: 20px">
    {{--footer--}}
    <table class="footer">
        <tr>
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; width: 70px; padding-bottom: 0px">
                Powered by -
            </td>
            <td style="font-size: 10px;font-weight: normal;  padding-left: 10px; padding-bottom: 0px; padding-top: 5px">
                <img src="{{public_path("images/sheba@3x.png")}}" style="height: 16px">
            </td>
            {{--<td style="font-size: 10px; opacity: 0.8; font-weight: normal;  padding-bottom: 0; text-align: right;">
                <span class="pageCounter">{{ $PAGE_NUM . "." . $PAGE_COUNT }}</span>
            </td>--}}
        </tr>
    </table>

    {{--header--}}
    <table class="header">
        <tr style="border: 0px">
            <td class="table1th" style="padding: 20px 20px 4px 20px; opacity: 0.6">
                ID
            </td>
            <td class="table1th" style="padding: 20px 20px 4px 20px; opacity: 0.6">
                Procurement Title
            </td>
            <td class="table1th" style="padding: 20px 20px 4px 20px; opacity: 0.6">
                Created On
            </td>
        </tr>
        <tr>
            <td class="boldText" style="padding: 0 20px 20px 20px">
                {{$procurement_details['id']}}
                <span style="padding-left: 10px; display: inline-block; margin-top: 3px">
                    <span class="status">
                    {{ucwords($procurement_details['status'])}}
                    </span>
                </span>
            </td>
            <td class="boldText" style="padding: 4px 20px 20px 20px;">
                {{$procurement_details['title']}}
            </td>
            <td class="boldText" style="padding: 4px 20px 20px 20px;">
                {{$procurement_details['created_at']}}
            </td>
        </tr>
    </table>

    {{--2nd table jhamela--}}
    <table  class="" style="width: 100%; margin-bottom: 20px">
        <tr>
            <th class="tableHeadRegular" colspan="3">
                General Information
            </th>
        </tr>
        <tr style="border: 0px">
            <td class="table1th" style="padding: 20px 20px 4px 20px;  opacity: 0.6">
                Labels
            </td>
            <td class="table1th" style="padding: 20px 20px 4px 20px;  opacity: 0.6">
                Delivery Date
            </td>
            <td class="table1th" style="padding: 20px 20px 4px 20px;  opacity: 0.6">
                Payment Method
            </td>
        </tr>
        <tr>
            <td style="font-size: 10px; font-weight: bold; padding: 4px 20px 20px 20px; opacity: 0.8">
                {{ implode($procurement_details['labels'], ',') }}
            </td>
            <td style="font-size: 10px; font-weight: bold; padding: 4px 20px 20px 20px; opacity: 0.8">
                {{$procurement_details['start_date']}} - {{$procurement_details['end_date']}}
            </td>
            <td style="font-size: 10px; font-weight: bold; padding: 4px 20px 20px 20px; opacity: 0.8">
                {{$procurement_details['payment_options']}}
            </td>
        </tr>
    </table>

    {{--3rd table--}}
    <table  class="" style="width: 100%;  margin-bottom: 20px">
        <tr>
            <th class="tableHeadRegular">
                Details
            </th>
        </tr>
        <tr>
            <td style="font-size: 10px; opacity: 0.6; font-weight: normal;  padding: 20px;">
                {{$procurement_details['long_description']}}
            </td>
        </tr>
    </table>

    @if(count($procurement_details['price_quotation']))
    <table  class="" style="width: 100%; table-spacing: 0px; margin-bottom: 20px;">
        <tr>
            <th class="tableHeadRegular"  colspan="5">
                Price Quotation
            </th>
        </tr>
        <tr>
            <td style="font-size: 10px; opacity: 0.8; font-weight: bold;  padding: 10px;border: solid 1px #d2d8e6;width:8%">
                SL NO
            </td>
            <td style="font-size: 10px; opacity: 0.8; font-weight: bold;  padding: 10px;border: solid 1px #d2d8e6;width: 30%">
                Item Name / Description
            </td>
            <td style="font-size: 10px; opacity: 0.8; font-weight: bold;  padding: 10px;border: solid 1px #d2d8e6;;width: 40%">
                Specification
            </td>
            <td style="font-size: 10px; text-align: right; opacity: 0.8; font-weight: bold;  padding: 10px;border: solid 1px #d2d8e6;">
                Unit
            </td>
            <td style="font-size: 10px; text-align: right; opacity: 0.8; font-weight: bold;  padding: 10px;border: solid 1px #d2d8e6;">
                Price
            </td>
        </tr>
        @foreach($procurement_details['price_quotation'] as $price_quotation)
            <tr>
                <td class="counterCell" style="font-size: 10px; opacity: 0.8; font-weight: normal;  padding: 10px;border: solid 1px #d2d8e6;">
                </td>
                <td style="font-size: 10px; opacity: 0.8; font-weight: normal;  padding: 10px;border: solid 1px #d2d8e6;">
                    {{$price_quotation['title']}}
                </td>
                <td style="font-size: 10px; opacity: 0.8; font-weight: normal;  padding: 10px;border: solid 1px #d2d8e6;">
                    {{$price_quotation['short_description']}}
                </td>
                <td style="font-size: 10px; opacity: 0.8; font-weight: normal; padding: 10px;border: solid 1px #d2d8e6; text-align: right; ">
                    {{json_decode($price_quotation['variables'],true)['unit']}}
                </td>
                <td style="font-size: 10px; opacity: 0.8; font-weight: normal;  padding: 10px;border: solid 1px #d2d8e6; text-align: right; ">&nbsp;</td>
            </tr>
        @endforeach
    </table>
    @endif

    @if(count($procurement_details['technical_evaluation']))
    <table  class="outerTable">
        <tbody>
        <tr>
            <td class="tableHeadRegular">
                Technical Evaluation
            </td>
        </tr>
        <tr>
            <td style="padding: 7px 20px 20px 20px">
        @foreach($procurement_details['technical_evaluation'] as $technical_evaluation)
            <tr>
                <td style="padding: 0px 20px 20px 20px">
                    <?php
                    $title = $technical_evaluation->title;
                    $options = $technical_evaluation->getOptions();
                    ?>
                    @if($technical_evaluation->isRadio())
                        {!! showRadio($title, $options) !!}
                    @elseif($technical_evaluation->isCheckBox())
                        {!! showCheckBoxes($title, $options)  !!}
                    @elseif($technical_evaluation->isText())
                        {!! showShortAnswer($title) !!}
                    @elseif($technical_evaluation->isNumber())
                        {!! showNumber($title) !!}
                    @elseif($technical_evaluation->isTextArea())
                        {!! showLongAnswer($title) !!}
                    @endif
                </td>
            </tr>
            @endforeach
            </td>
            </tr>
        </tbody>
    </table>
    @endif

    @if(count($procurement_details['company_evaluation']))
    <table  class="outerTable">
        <tr>
            <th class="tableHeadRegular">
                Company Evaluation
            </th>
        </tr>
        <tr>
            <td style="padding: 7px 20px 20px 20px">
            @foreach($procurement_details['company_evaluation'] as $company_evaluation)
            <tr>
                <td style="padding: 0px 20px 20px 20px">
                    <?php
                    $title = $company_evaluation->title;
                    $options = $company_evaluation->getOptions();
                    ?>
                    @if($technical_evaluation->isRadio())
                        {!! showRadio($title, $options) !!}
                    @elseif($technical_evaluation->isCheckBox())
                        {!! showCheckBoxes($title, $options) !!}
                    @elseif($technical_evaluation->isText())
                        {!! showShortAnswer($title) !!}
                    @elseif($technical_evaluation->isNumber())
                        {!! showNumber($title) !!}
                    @elseif($technical_evaluation->isTextArea())
                        {!! showLongAnswer($title) !!}
                    @endif
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
