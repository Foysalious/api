<!DOCTYPE html>

<html lang="en">

<head>
    <!-- start: Meta -->
    <title>Invoice 1.1</title>
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


        ​

        /*new styles end*/
    </style>


</head>

<body style="margin-top: 55px; margin-bottom: 22px; font-family: Lato;">

{{--<body style="margin: 50px 30px; font-family: Lato; ">--}}
<table class="documentTitle">
    <tr>
        <td>Invoice</td>
    </tr>
    <tr>
        <td><hr></td>
    </tr>
</table>

<table class="invoiceInfo">
    <tr>
        <td>Invoice ID : </td>
        <td>#0A4HA500</td>
    </tr>
    <tr>
        <td>Invoice Submitted Date :  </td>
        <td>21 Feb , 2020</td>
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
                    <td>Fahim Razzaq</td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td>House no : 01 ; Road no : 01;
                        Mirpur ; Dhaka.</td>
                </tr>
                <tr>
                    <td>Mobile:</td>
                    <td>01617000000</td>
                </tr>
            </table>
        </td>
        <td>
            <table style="border: 0">
                <tr>
                    <td>Name:</td>
                    <td>Fahim Razzaq</td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td>House no : 01 ; Road no : 01;
                        Mirpur ; Dhaka.</td>
                </tr>
                <tr>
                    <td>Mobile:</td>
                    <td>01617000000</td>
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
    <tr class="itemsList">
        <td class="itemsList border-left padding-left">Keyboard</td>
        <td class="itemsList">A4 Tech</td>
        <td class="itemsList">10</td>
        <td class="itemsList">৳500</td>
        <td class="itemsList border-right">৳5000</td>
    </tr>
    <tr class="itemsList">
        <td class="itemsList border-left padding-left">Keyboard</td>
        <td class="itemsList">A4 Tech</td>
        <td class="itemsList">10</td>
        <td class="itemsList">৳500</td>
        <td class="itemsList border-right">৳5000</td>
    </tr>
    <tr class="total">
        <td colspan="3"></td>
        <td>Sub total</td>
        <td>৳13545</td>
    </tr>
    <tr class="total">
        <td colspan="3"></td>
        <td>Due</td>
        <td>0</td>
    </tr>
    <tr>
        <td  colspan="5"><hr></td>
    </tr>
    <tr class="total">
        <td colspan="3"></td>
        <td>Grand Total</td>
        <td>৳15000</td>
    </tr>
</table>

{{--
<table  class="tableHeadRegular header" style="width: 100%;  margin-bottom: 20px; padding: 0px; background-color: #fff;border: none " >
    <tr>
        <td style="opacity: 0.8; font-family: Lato; font-size: 20px; font-weight: bold; color: #000000;">Employee Expense</td>
        <td style="text-align: right">
            <img src="https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/images/sheba_logo_blue.png" alt="" width="150px">
        </td>
    </tr>
    <tr>
        <td><hr style=" color: #d1d7e6; width: 720px"></td>
        --}}
{{--        <td><hr style=" color: #d1d7e6; width: 100%;"></td>--}}{{--

    </tr>
</table>

<table style="width: 100%; border: none">
    <tr>
        <td style="width: 60%; border : none; vertical-align: top;">
            <table style="width: 100%; border : none">
                <tr>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">Employee Name</td>
                    <td style="vertical-align: top; font-family: Lato; font-size: 10px; color: #000000; opacity: 0.8; padding-bottom: 13px">:</td>
                </tr>

                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Designation</td>
                    <td style="font-family: Lato; padding-bottom: 13px;  font-size: 10px; color: #000000; opacity: 0.8">:</td>
                </tr>

                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Department</td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">:</td>
                </tr>


                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Mobile Number</td>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">:</td>
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
                    </td>
                </tr>

                <tr>
                    <td style="font-family: Lato; padding-bottom: 13px; font-size: 10px; color: #000000; opacity: 0.8">Month</td>
                    <td style="font-family: Lato; padding-bottom: 13px;  font-size: 10px; color: #000000; opacity: 0.8">:</td>
                    <td style="padding-left: 10px; padding-bottom: 13px; font-family: Lato; font-size: 10px; font-weight: bold; color: #000000; opacity: 0.8">
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
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

--}}
{{-- Expense Table --}}{{--

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

    --}}
{{--    @foreach($bid_details['price_quotation'] as $price_quotation)--}}{{--

    <tbody>
        <tr>
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px 10px;border: solid 1px #d2d8e6;">
            </td>
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
            </td>
            <td style="font-size: 10px; opacity: 0.8; font-weight: normal; font-family: Lato; padding: 5px;border: solid 1px #d2d8e6;">
            </td>
            <td style="font-size: 10px; opacity: 0.8; padding: 5px 10px; font-family: Lato;border: solid 1px #d2d8e6; text-align: right; ">
            </td>
        </tr>
    @endforeach


    </tbody>


    <tfoot>
    <tr>
        <td></td>
        <td></td>
        <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: Lato; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;">
            Sum
        </td>
        <td style="font-size: 10px; font-weight: bold; opacity: 0.8; font-weight: bold; font-family: Lato; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;">
        </td>
    </tr>

    </tfoot>


</table>
--}}
{{--
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
</table>--}}

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
