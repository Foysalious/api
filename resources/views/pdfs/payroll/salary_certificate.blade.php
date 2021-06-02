<!DOCTYPE html>

<html lang="en">

<head>
    <!-- start: Meta -->
    <title>Salary Certificate</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keyword" content="">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        @media print {
            table { page-break-after: auto; page-break-inside: auto; }
            tr    { page-break-inside:avoid; page-break-after:auto }
            /*td    { page-break-inside:avoid; !*page-break-after:auto*! }*/
            /*thead { display: table-header-group}
            tfoot { display: table-footer-group}*/
        }
        ​@font-face {
            font-family: 'Poppins', sans-serif;
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

        .tableHeadRegular{
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
        .header{
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
            font-weight: 500;
            color: #000000;
        }

        .salary-certificate__text {
            margin: 0;
            padding: 0;
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 300;
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
        /* Footer */
        .footer {
            width: 100%;
            font-family: 'Poppins', sans-serif;
            position: fixed;
            left: 0;
            bottom: 15em;
            border: none;
            font-size: 12px;
        }

        ​/*new styles end*/
    </style>


</head>

<body style="margin-top: 20px; font-family: 'Poppins', sans-serif;">

{{--<body style="margin: 50px 30px; font-family: 'Poppins', sans-serif; ">--}}

<table class="header">
    <tr>
        @if($salary_certificate_info['business_logo'])
            <td class="text-left"><img src="{{ $salary_certificate_info['business_logo'] }}" height="65"/></td>
        @endif
        @if($salary_certificate_info['business_logo'])
            <td class="text-right"><p class="company-name">{{$salary_certificate_info['business_name']}}</p>
            </td>
        @else
            <td class="text-left"><p class="company-name">{{$salary_certificate_info['business_name']}}</p>
            </td>
        @endif
    </tr>
    <tr>
        <td><hr style=" color: #d1d7e6; width: 720px"></td>
    </tr>

    <tr>
        <td>
            <p class="salary-certificate__text">Salary Certificate</p>
        </td>
    </tr>
</table>

<table class="salary-certificate__created-date" style="width: 40%; border: none">
    <tr>
        <td>Date</td>
        <td>:</td>
        <td>{{ $salary_certificate_info['created_date'] }}</td>
    </tr>
</table>

<table class="employee__info">
    <tr>
        <td>
            <span style="line-height: 1.2">
                This is to certify that <span style="font-weight: 600">{{ $salary_certificate_info['employee_info']['name'] }},</span>
            {{ $salary_certificate_info['employee_info']['designation'] }},
            {{ $salary_certificate_info['employee_info']['department'] }} Department is a
            permanent employee of <span style="font-weight: 600">{{ $salary_certificate_info['business_name'] }}</span>
            from {{ $salary_certificate_info['employee_info']['joining_date'] }}.
            </span>
        </td>
    </tr>

    <tr>
        <td>
            <p style="padding-top: 16px; margin: 0;">The details of his/her monthly salary are provided below.</p>
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
    @foreach($salary_certificate_info['salary_info']['salary_breakdown'] as $key => $value)
        <tr style="width: 100%">
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px 10px;border: solid 1px #d2d8e6;width:500px">
                {{ $value['title'] }}
            </td>
            <td style="font-size: 12px; opacity: 0.8; font-weight: normal; font-family: 'Poppins', sans-serif; padding: 5px;border: solid 1px #d2d8e6;width:120px;text-align: right">
                {{ $value['amount'] }}
            </td>
        </tr>
    @endforeach
    </tbody>

    <tfoot>
    <tr>
        <td style="font-size: 12px; font-weight: bold; opacity: 0.8; font-family: 'Poppins', sans-serif; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
            Total
        </td>
        <td style="font-size: 12px; font-weight: bold; opacity: 0.8; font-family: 'Poppins', sans-serif; text-align: right; padding: 5px;border-right: solid 1px #d2d8e6;width: 100%">
            {{ $salary_certificate_info['salary_info']['gross_salary'] }}
        </td>
    </tr>

    </tfoot>

</table>

<table class="salary-amount_in_words" style="width: 40%; border: none">
    <tr>
        <td>In words</td>
        <td>:</td>
        <td>{{ $salary_certificate_info['salary_info']['gross_salary_in_word'] }} Taka Only</td>
    </tr>
</table>

<table class="employee__info">
    <tr>
        <td>
            <span style="line-height: 1.2">
                This is a system generated salary certificate which has been issued on the request of
            <span style="font-weight: 600">{{ $salary_certificate_info['employee_info']['name'] }}</span>.
            The <span style="font-weight: 600">{{ $salary_certificate_info['business_name'] }}</span>
            management is responsible for the content of this certificate only.
            </span>
        </td>
    </tr>
</table>

<table class="footer">
    <tr>
        <td>Authorized by</td>
    </tr>
    <tr>
        <td style="padding-top: 40px"><hr style="margin-left: 0px; color: #d1d7e6; width: 130px"></td>
    </tr>
    <tr>
        <td>Name</td>
    </tr>
    <tr>
        <td>Designation</td>
    </tr>
</table>

</body>

</html>
