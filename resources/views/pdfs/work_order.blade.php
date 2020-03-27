<!DOCTYPE html>

<html lang="en">
<head>
    <!-- start: Meta -->
    <title>Workorder</title>
    <meta name="description" content="">
    <meta name="author" content="Fazal Mahmud Niloy">
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

            /*td    { page-break-inside:avoid; !*page-break-after:auto*! }*/
            /*thead { display: table-header-group}
            tfoot { display: table-footer-group}*/
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
        .invoiceInfo {
            border: 0;
        }
        .addresses {
            width: 100%;
            border: 0;
            margin-top: 50px;
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
        }
        .total {
            font-weight: bold;
        }
        /*invoice page end*/

        ​

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


        ​

        /*new styles end*/
    </style>
</head>

<body style="margin-top: 55px; margin-bottom: 22px; font-family: Lato;">

{{--FOOTER START--}}
<table class="footer">
    <tr>
        <td colspan="2" class="footerPrompt">This is a digital version of W/O ,No signature is required here. </td>
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
        <td>Workorder</td>
    </tr>
    <tr>
        <td><hr></td>
    </tr>
</table>

<table class="invoiceInfo">
    <tr>
        <td>Work Order no: </td>
        <td>{{ $work_order['code'] }}</td>
    </tr>
</table>

<table class="addresses">
    <tr class="addressRow">
        <td style="margin-bottom: 16px">To Address</td>
        <td style="margin-bottom: 16px">For Address</td>
    </tr>
    <tr>
        <td>
            <table style="border: 0">
                <tr>
                    <td>Name:</td>
                    <td>{{ $work_order['to']['name'] }}</td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td>{{ $work_order['to']['address'] }}</td>
                </tr>
                <tr>
                    <td>Mobile:</td>
                    <td>{{ $work_order['to']['mobile'] }}</td>
                </tr>
            </table>
        </td>
        <td>
            <table style="border: 0">
                <tr>
                    <td>Name:</td>
                    <td>{{ $work_order['from']['name'] }}</td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td>{{ $work_order['from']['address'] }}</td>
                </tr>
                <tr>
                    <td>Mobile:</td>
                    <td>{{ $work_order['from']['mobile'] }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="itemsTable">
    <tr class="itemsSpec">
        <td class="itemsSpecLabel border-left padding-left ">Item Name</td>
        <td class="itemsSpecLabel">Specification</td>
        <td class="itemsSpecLabel">Unit</td>
        <td class="itemsSpecLabel">Unit Price</td>
        <td class="itemsSpecLabel border-right">Total Price</td>
    </tr>

    @foreach($work_order['items'] as $item)
        <tr class="itemsList">
            <td class="itemsList border-left padding-left">{{ $item['title'] }}</td>
            <td class="itemsList">{{ $item['short_description'] }}</td>
            <td class="itemsList">{{ $item['unit'] }}</td>
            <td class="itemsList">৳ {{ $item['unit_price'] }}</td>
            <td class="itemsList border-right">৳ {{ $item['total_price'] }}</td>
        </tr>
    @endforeach

    <tr class="total">
        <td colspan="3"></td>
        <td>Sub total</td>
        <td>৳ {{ $work_order['sub_total'] }}</td>
    </tr>
    <tr class="total">
        <td colspan="3"></td>
        <td>Due</td>
        <td>৳ {{ $work_order['due'] }}</td>
    </tr>
    <tr>
        <td  colspan="5"><hr></td>
    </tr>
    <tr class="total">
        <td colspan="3"></td>
        <td>Grand Total</td>
        <td>৳ {{ $work_order['grand_total'] }}</td>
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
