<!DOCTYPE html>

<html lang="en">

<head>
    <!-- start: Meta -->
    <title>Tax Certificate</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keyword" content="">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
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

        ​

        @font-face {
            font-family: 'Poppins', sans-serif;
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

        .tableHeadRegular {
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: bold;
            padding: 9px 20px;
            text-align: left;
            background-color: #fff8f8fb;
        }


        @page {
            margin-top: 20px;
        }

        .header {
            top: 0;
            left: 0;
            width: 100%;
            position: fixed;
            padding: 0;
            margin: 110px 0 20px 0;
            background-color: #fff;
            border: none;
        }

        .company-name {
            margin: 0;
            padding-top: 27px;
            font-family: 'Poppins', sans-serif;
            opacity: 0.8;
            font-size: 18px;
            color: #000000;
            font-weight: bold;
        }

        .salary-certificate__text {
            margin: 0;
            padding: 0;
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #000000;
        }

        .salary-certificate__created-date {
            margin: 48px 0 0 0;
            padding: 0;
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-size: 9px;
            color: #000000;
        }

        .employee__info {
            margin: 16px 0 32px 0;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #484b4f;
            text-align: justify;
            line-height: 1.5;
            border: none;
        }

        .salary-amount_in_words {
            padding: 0;
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-size: 10px;
            font-weight: 500;
            color: #000000;
        }

        .under_line {
            display: flex;
        }

        .under_line:after {
            display: block;
            content: " ";
            border-bottom: 1px solid;
            flex: 1 1 auto;
            width: 30px;
        }

        /* Footer */
        .footer {
            width: 100%;
            font-family: 'Poppins', sans-serif;
            position: fixed;
            left: 0;
            bottom: 15em;
            border: none;
            font-size: 12px;
            margin-top: 5em;
        }

        .tax_certificate_headings {
            margin-top: -20px;
        }

        ​

        /*new styles end*/
    </style>


</head>

<body style="margin-top: 20px; font-family: 'Poppins', sans-serif;">

{{--<body style="margin: 50px 30px; font-family: 'Poppins', sans-serif; ">--}}

<table class="header">
    <tr>
        @if($business_logo)
            <td class="text-left"><img src="{{ $business_logo }}" height="65"/></td>
        @endif
        <td class="text-right"><p class="company-name">{{$business_name}}</p></td>
    </tr>
    <tr>
        <td>
            <hr style=" color: #d1d7e6; width: 720px">
        </td>
    </tr>
</table>
<table class="footer">
    <tr>
        <td>Name of Authorized Person :</td>
    </tr>
    <tr>
        <td>Designation of Authorized Person :</td>
    </tr>
    <tr>
        <td>{{$business_name}}</td>
    </tr>
    <tr>
        <td> Mobile No :</td>
    </tr>
</table>
<div class="main_content">
    <table class="employee__info tax_certificate_headings">
        <tr>
            <td>
                <p class="salary-certificate__text">Certificate of Payment of Salary</p>
                <p class="salary-certificate__text">Income Year :</p>
                <p class="salary-certificate__text">Assessment Year :</p>
            </td>
        </tr>
    </table>
    <table class="employee__info">
        <tr>
            <td>
                <span style="line-height: 1.2">
                    This is to certify that {{$employee_profile->name}}, {{$employee_role->name}}, {{$business_name}} was paid Tk. {{$total_gross_salary}}, ({{$gross_amount_in_word}} Taka only) from {{$period}} as salary and benefits, break up of which  is as follows:
                </span>
            </td>
        </tr>
    </table>
    <table class="tableHead" style="width: 100%; margin-bottom: 20px; position: relative">

        <thead>
        <tr class="tableHeadRegular" style="background: #f8f8fb; width: 100%">
            <td style="width:80%;font-size: 12px; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 10px;border: solid 1px #d2d8e6;">
                Components
            </td>
            <td style="width:20%; font-size: 12px; text-align: right; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 10px;border: solid 1px #d2d8e6;">
                Amount
            </td>
        </tr>
        </thead>

        <tbody>
        @foreach($gross_salary_breakdown as $salary_breakdown)
            <tr style="width: 100%">
                <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:500px">
                    {{$salary_breakdown['value']}}
                </td>
                <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:120px;text-align: right">
                    {{$salary_breakdown['amount']}}
                </td>
            </tr>
        @endforeach
        </tbody>

        <tfoot>
        <tr>
            <td style="font-size: 12px; font-weight: bold; opacity: 0.8; font-family: 'Poppins', sans-serif; text-align: left; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
                Net Payable
            </td>
            <td style="font-size: 12px; font-weight: bold; opacity: 0.8; font-family: 'Poppins', sans-serif; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
                {{$net_payable}}
            </td>
        </tr>

        </tfoot>

    </table>
    <table class="employee__info">
        <tr>
            <td>
                <span style="line-height: 1.2">
                    During the income year <span style="text-decoration: underline; text-underline-offset: 0.2em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> an amount of TK. {{$yearly_tax}}, ({{$yearly_tax_in_word}} Taka only) was deducted at source from his salary as income tax as per following details:
                </span>
            </td>
        </tr>
    </table>
    <table class="tableHead" style="width: 100%; margin-bottom: 20px; position: relative">

        <thead>
        <tr class="tableHeadRegular" style="background: #f8f8fb; width: 100%">
            <td style="width:20%;font-size: 12px; text-align: center; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;">
                Date of deposit
            </td>
            <td style="width:20%; font-size: 12px; text-align: center; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;">
                Bank Name
            </td>
            <td style="width:20%;font-size: 12px; text-align: center; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;">
                Challan No
            </td>
            <td style="width:20%; font-size: 12px; text-align: center; opacity: 0.8; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;">
                Total Challan Amount
            </td>
            <td style="width:20%; font-size: 12px; text-align: center; opacity: 1; font-weight: bold; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;">
                Employee Tax Amount
            </td>
        </tr>
        </thead>

        <tbody>
        <tr style="width: 100%">
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:20%; height:22px;"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:20%; height:22px;text-align: right"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:20%; height:22px;"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:20%; height:22px;text-align: right"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:20%; height:22px;"></td>
        </tr>
        <tr style="width: 100%">
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:20%; height:22px;"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:20%; height:22px;text-align: right"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:20%; height:22px;"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:20%; height:22px;text-align: right"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:20%; height:22px;"></td>
        </tr>
        <tr style="width: 100%">
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:20%; height:22px;"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:20%; height:22px;text-align: right"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:20%; height:22px;"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:20%; height:22px;text-align: right"></td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:20%; height:22px;"></td>
        </tr>
        </tbody>
    </table>
    <table class="employee__info">
        <tr>
            <td>
                <span style="line-height: 1.2">
                    This certificate is given by us, being responsible for accounting function of {{$business_name}} without any risk and responsibility.
                </span>
            </td>
        </tr>
    </table>
</div>
</body>

</html>
