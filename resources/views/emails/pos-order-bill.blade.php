<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Bill</title>
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
            background: #EEEEEE;
            text-align: center;
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

    </style>
</head>

<body>
<header class="clearfix">
    <div>
        <div id="logo">
            <img src="{{ $order->partner->logo }}" class="img-responsive">
        </div>
        <div id="company">
            <h2 class="name">{{ $order->partner->name }}</h2>
            <div>{{ $order->partner->address }}</div>
            <div>{{ $order->partner->getContactNumber() }}</div>
        </div>
    </div>
</header>
<main>
    <div id="details" class="clearfix">
        <div id="client">
            <div class="to">TO:</div>
            <h2 class="name">{{ $order->customer->profile->name }}</h2>
            <div class="email">{{ $order->customer->profile->email }}</div>
        </div>
        <div id="invoice">
            <h1># {{ $order->id }}</h1>
            <div class="date">Date of Bill: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
        </div>
    </div>
    <div id="order">
        <div class="pull-left">
            ORDER NUMBER : # {{ $order->id }}
        </div>
        <div class="text-right">
            VENDOR :  {{ $order->partner->name }}
        </div>
    </div>

    <table border="0" cellspacing="0" cellpadding="0">
        @include('emails._invoice')
    </table>

    <div id="notices">
        <div class="notice">"No Tips" policy applicable.</div>
    </div>
</main>
<footer>
    This was created on a computer and is valid without the signature and seal.
</footer>
</body>
</html>