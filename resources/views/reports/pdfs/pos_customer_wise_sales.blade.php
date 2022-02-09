<!DOCTYPE html>
<html lang="en">
<head>
    <title>Product wise sales report</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <style type="text/css">
        @import url('https://fonts.maateen.me/kalpurush/font.css');

        .page-break {
            page-break-after: always;
        }

        .break-before {
            page-break-before: auto;
        }
        body {
            font-family: 'kalpurush', sans-serif!important;
            color: #4a4a4a;
            font-style: normal;
            font-weight: normal;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            font-family: 'kalpurush', sans-serif!important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #ddd !important;
        }
        .table-bordered {
            border: 1px solid #ddd;
        }

        td, th {
            text-align: left;
            padding: 20px;
            font-weight: normal;
            box-sizing: border-box;
            position: relative;
            vertical-align: baseline;
        }

        th {
            padding: 10px;
        }

        .table-head {
            background-color: #ededed;
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
            font-family: 'kalpurush', sans-serif!important;
        }

        .heading .sub-text {
            font-size: .9rem;
            font-family: 'kalpurush', sans-serif!important;
        }

        .table-head th {
            font-family: 'kalpurush', sans-serif!important;
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
            display: block;
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
<table style="max-width: 800px;margin: auto;min-width: 600px;page-break-before: avoid;">
    <tbody>
    <tr>
        <td>
            <div class="heading">
                <h2>{{ ucfirst($partner->name) }}</h2>
                <h4 class="sub-heading"> Customer Wise Sales Report </h4>
                <span class="sub-text">{{'From '.$from->format('jS F Y').' To '.$to->format('jS F Y')}}</span>
            </div>
        </td>
    </tr>
    </tbody>
</table>
<div>
    <table class="table table-bordered">
        <thead>
        <tr class="table-head">
            <th>Customer Name </th>
            <th> Order Count</th>
            <th> Sales Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php $totalOrder = $totalPrice = 0;?>
        @foreach($data as $item)
            <tr>
                <?php $totalPrice += (float)$item['sales_amount'];
                $totalOrder += (float)$item['order_count']; ?>
                <td>{{ $item['customer_name'] }}</td>
                <td>{{ number_format((float)$item['order_count'],2) }}</td>
                <td>{{ number_format((float)$item['sales_amount'],2) }}</td>
            </tr>
        @endforeach

        <tr style="page-break-after: always" class="table-head">
            <td><span class="font-weight-bold"> Total</span></td>
            <td><span class="font-weight-bold">{{ number_format((float)$totalOrder,2) }}</span></td>
            <td><span class="font-weight-bold">{{ number_format((float)$totalPrice,2) }}</span></td>
        </tr>
        </tbody>
    </table>
</div>
<footer align="center" class="footer">
    <div class="text-center pt-1 w-100" id="footer"><span>Powered by <a
                    href="{{config('sheba.partners_url')}}">sManager</a></span></div>
</footer>
</body>
</html>
