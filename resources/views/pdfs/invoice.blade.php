<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
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
                <div class="logo">
                    <img width="150px" style="float:right;padding: 10px;" class="logo" src="https://s3.ap-south-1.amazonaws.com/cdn-shebadev/admin_assets/assets/images/login-logo.png" class="img-responsive">
                </div>
            </td>
        </tr>
    </table>
</header>
@include('pdfs._order_details')
<main>
    <div id="details" class="clearfix">
{{--        <div id="client">--}}
{{--            <div class="to">{{ $type }} TO:</div>--}}
{{--            <h2 class="name">{{ $partner_order->order->delivery_name }}</h2>--}}
{{--            <div class="address">{{ $partner_order->order->delivery_address }}</div>--}}
{{--            <div class="email">{{ $partner_order->order->delivery_mobile }}</div>--}}
{{--        </div>--}}
        <div id="invoice">
{{--            <h1 style="text-transform: uppercase">{{ $type }} {{ $partner_order->id }}</h1>--}}
{{--            <div class="date">Generated on: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>--}}
            <?php
                $job = $partner_order->lastJob();
            ?>
            @if($job->status === 'Served')
            <div class="date">Served Date: {{ $partner_order->closed_at->format('d/m/Y')  }}</div>
            @endif
        </div>
    </div>
{{--    <div id="order">--}}
{{--        <div class="pull-left">--}}
{{--            ORDER NUMBER : {{ $partner_order->order->code() }}--}}
{{--        </div>--}}
{{--        <div class="text-right">--}}
{{--            RESOURCE :  {{ $job->resource?$job->resource->profile->name :"N\A"}}--}}
{{--        </div>--}}
{{--    </div>--}}
    <table border="0" cellspacing="0" cellpadding="0">
        @if($partner_order->order_id > config('sheba.last_order_id_for_old_version'))
            @include('pdfs._invoice_v2')
        @else
            @include('pdfs._invoice_v1')
        @endif
    </table>

    {{--Material goes here--}}
    <?php
    $materials_sl_no = 1;
    $has_material = false;
    foreach($partner_order->jobs as $job){
        if(count($job->usedMaterials) > 0){
            $has_material = true;
            break;
        }
    }
    ?>
    @if($has_material)
        <table class="materials-table">
            <tr>
                <th class="text-left">JOB CODE</th>
                <th class="text-left">#</th>
                <th class="material-name">ADDITIONAL</th>
                <th class="text-right">PRICE</th>
            </tr>
            @forelse($partner_order->jobs as $job)
                <?php $materials_sl_no = 0; ?>
                @if(count($job->usedMaterials) > 0)
                    @forelse($job->usedMaterials as $material)
                        <?php $materials_sl_no++; ?>
                        <tr>
                            <td class="m-job-code" style="background-color: #f5f5f5;"> @if($materials_sl_no === 1) {{ $job->code() }} @endif </td>
                            <td style="background-color: #f5f5f5;"> {{ $materials_sl_no }} </td>
                            <td style="background-color: #f5f5f5;"> {{ $material->material_name }}</td>
                            <td class="text-right" style="text-align: right; background-color: #f5f5f5;"> {{ $material->material_price }}</td>
                        </tr>
                    @empty
                    @endforelse
                    <tr class="total-job-material">
                        <td colspan="2"></td>
                        <td class="text-right"> Total additional cost for JOB #{{ $job->code() }}  </td>
                        <td class="text-right" style="text-align: right;"> {{ $job->materialPrice }}</td>
                    </tr>
                @endif
            @empty
            @endforelse
        </table>
    @endif

    {{--<div id="thanks">Thank you!</div>--}}
    <div id="notices">
        {{--<div>NOTICE:</div>--}}
        <div class="notice">Seven (07) days service warranty.</div>
        <div class="notice">"No Tips" policy applicable.</div>
    </div>
    {{--<div id="notices">--}}
        {{--<div>NOTICE:</div>--}}
        {{--<div class="notice">A finance charge of 1.5% will be made on unpaid balances after 30 days.</div>--}}
    {{--</div>--}}
    @if($job->status !== 'Served')
        <div class="quote">
            <br>*** Total cost may be changed due to the nature of the service.</div>
    @endif
</main>
<footer>
    This was created on a computer and is valid without the signature and seal.
</footer>
</body>
</html>
