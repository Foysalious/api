<!DOCTYPE html>
<html lang="en" >
<head>
    <title>Product wise sales report</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style type="text/css">
        @import url('https://fonts.maateen.me/mukti/font.css');

        .page-break {
            page-break-after: always;
        }

        .break-before {
            page-break-before: auto;
        }
        body {
            font-family: 'Mukti',  'Roboto',sans-serif;
            color: #4a4a4a;
            font-style: normal;
            font-weight: normal;
        }

        .heading {
            text-align: center;
            margin-top: 20px;
        }

        .heading h2 {
            font-size: 1.5rem;
        }

        .heading .sub-heading {
            font-size: 1rem;
            font-family: 'Mukti','Roboto',sans-serif;
        }

        .heading .sub-text {
            font-size: .9rem;
            font-family: 'Mukti','Roboto',sans-serif;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            font-family: 'Mukti','Roboto',sans-serif;
        }

        th {
            padding: 10px;
        }

        .table-head {
            background-color: #ededed;
            font-weight: normal;
        }

        .table-head th {
            font-family: 'Mukti','Roboto',sans-serif;
            font-weight: normal;
        }
        @page {
            margin: 40px;
            padding: 2cm;
            footer: page-footer;
        }

        @media print {

            .table {
                page-break-inside: auto !important;
                page-break-before: avoid !important;
            }

            .table tbody tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }

        }

        /** Define the footer rules **/
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            text-align: center;
            line-height: 1.5cm;
        }



        #pageCounter:before {
            content: "Page " counter(page) ;
        }

        #counter {
            position: fixed;
            bottom: 0cm;
            left: auto;
            right: 30px;
            height: 2cm;
            text-align: center;
            line-height: 1.5cm;
            width: 120px;
        }
    </style>
</head>
<body align="center">
<div>
    <p> Deposit: {{ $stats["deposit"]}}, <span style="padding-left:100px"></span> <span>Due: {{ $stats["due"]}}</span></p>
    <table class="table table-bordered">
        <thead>
        <tr class="table-head">
            <th>name</th>
            <th>mobile</th>
            <th>Balance</th>
            <th>type</th>
            <th>Head</th>
            <th>Transaction</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $item)
            <tr>
                <td>{{$item['customer_name']}}</td>
                <td>{{$item['customer_mobile']}}</td>
                <td>{{number_format((float)$item['balance'],2)}}</td>
                <td>{{$item['balance_type']}}</td>
                <td>{{$item['head']}}</td>
                <td>{{$item['transaction_type']}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
