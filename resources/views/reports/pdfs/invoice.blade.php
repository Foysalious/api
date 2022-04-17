<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@200&display=swap" rel="stylesheet">
    <title>{{ $type }}</title>
    <style>
        @font-face {
            font-family: SourceSansPro;
            /*src: url(SourceSansPro-Regular.ttf);*/
        }

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #1b4280;
            text-decoration: none;
        }

        body {
            position: relative;
            width: 100%;
            height: auto;
            margin: 0 auto;
            color: #555555;
            background: #FFFFFF;
            font-family: Arial, sans-serif;
            font-size: 14px;
            /*font-family: SourceSansPro;*/
        }

        header {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #AAAAAA;
        }

        #logo {
            float: left;
            margin-top: 8px;
        }

        #logo img {
            height: 70px;
        }

        #company {
            /*float: right;*/
            text-align: right;
        }

        #details {
            margin-bottom: 20px;
        }

        #client {
            width: 50%;
            padding-left: 6px;
            border-left: 6px solid #1b4280;
            float: left;
        }

        #client .to {
            color: #777777;
        }

        h2.name {
            font-size: 1.4em;
            font-weight: normal;
            margin: 0;
        }

        #invoice {
            /*float: right;*/
            text-align: right;
        }

        #invoice h1 {
            color: #1b4280;
            font-size: 2.4em;
            line-height: 1em;
            font-weight: normal;
            margin: 0  0 10px 0;
        }

        #invoice .date {
            font-size: 1.1em;
            color: #777777;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 10px;
            /*background: #EEEEEE;*/
            /*text-align: center;*/
            border-bottom: 1px solid #FFFFFF;
        }

        table td {
            padding: 8px;
        }

        table th {
            white-space: nowrap;
            font-weight: bold;
        }

        table td{
            font-size: 0.9em;
        }

        table td h3{
            color: #1b4280;
            font-size: 1.0em;
            font-weight: normal;
            margin: 0 0 0.2em 0;
        }

        table td span{
            font-size: 0.8em;
        }

        table .no {
            /*color: #FFFFFF;*/
            font-size: 1.2em;
            background: #DDDDDD;
            text-align: left;
        }

        table .desc {
            text-align: left;
            width:220px;
        }

        table .code {
            background: #DDDDDD;
            text-align: left;
        }

        table .unit {
            background: #DDDDDD;
            text-align: center;
        }

        table .qty {
            text-align: center;
        }

        table .total {
            background: #13b4d5;
            color: #FFFFFF;
            text-align: right;
        }

        /*table td.unit,*/
        /*table td.qty,*/
        table td.total {
            font-size: 1.2em;
        }

        table tbody tr:last-child td {
            border: none;
        }

        table tfoot td {
            padding: 10px;
            background: #FFFFFF;
            border-bottom: none;
            font-size: 1.0em;
            white-space: nowrap;
            border-top: 1px solid #AAAAAA;
        }

        table tfoot tr:first-child td {
            border-top: none;
        }

        table tfoot tr:last-child td {
            color: #1b4280;
            font-size: 1.4em;
            border-top: 1px solid #1b4280;

        }

        table tfoot tr td:first-child {
            border: none;
        }

        #thanks{
            font-size: 2em;
            margin-bottom: 50px;
        }

        #notices{
            padding-left: 6px;
            border-left: 6px solid #1b4280;
        }

        #notices .notice {
            font-size: 0.8em;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #AAAAAA;
            padding: 8px 0;
            text-align: center;
        }

        #order{
            padding: 20px 0;
        }

        .s-price{
            text-align: right;
        }

        .text-left{
            text-align: left;
        }

        .text-right{
            text-align: right;
        }

        .pull-left{
            float: left;
        }

        table.materials-table tbody > tr > td{
            text-align: left;
        }

        .material-name{
            width: 400px;
            text-align: left;
        }

        .total-job-material{
            background-color: #eeeeee;
            font-weight: bold;
            color:#111;
        }

        .m-job-code{
            color:#1b4280;
            font-weight: bold;
            font-size: 1.0em;
        }

        #client .to{
            text-transform: uppercase;
        }
        .header-style {
            font-weight: bold;
            font-size: 30px;
            width: 300px;
            background: #FFFFFF;
            text-align: left;
            padding: 0;
        }
        .logo{
            float:right;
        }

    </style>
</head>

<body>
<header class="clearfix">
{{--    <div>--}}
{{--    <div id="logo">--}}
{{--        <img src="https://s3.ap-south-1.amazonaws.com/cdn-shebadev/admin_assets/assets/images/login-logo.png" class="img-responsive">--}}
{{--    </div>--}}
{{--    <div id="company">--}}
{{--        <h2 class="name">Sheba.xyz</h2>--}}
{{--        <div>16516</div>--}}
{{--        <div><a href="mailto:info@sheba.xyz">info@sheba.xyz</a></div>--}}
{{--    </div>--}}
{{--    </div>--}}
    <table width="100%">
        <tr>
            <td class="header-style">
                Service Provider Order Statement
            </td>
            <td class="text-right" style="background: #FFFFFF;">
                <img width="150px" style="float:right;padding: 10px;" class="logo"
                     src="{{asset('assets/images/logo_coloured.png')}}" alt="Logo"/>
            </td>
        </tr>
    </table>
</header>
@include('pdfs._order_details')

<footer>
    This is a system generated statement. No signature is required.
</footer>
</body>
</html>
