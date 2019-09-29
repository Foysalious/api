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
<table style="max-width: 800px;margin: auto;min-width: 600px">
    <tbody>
    <tr>
        <td>
            <div class="heading">
                <h2>{{ucfirst($partner->name)}}</h2>
                <h4 class="sub-heading"> Product Wise Sales Report</h4>
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
            <th>Name</th>
            <th> Quantity</th>
            <th> Price</th>
            <th>Avg Price</th>
        </tr>
        </thead>
        <tbody>
        <?php $totalQuantity = $totalPrice = 0;?>
        @foreach($data as $item)
            <tr>
                <?php $totalPrice += (float)$item['total_price'];
                $totalQuantity += (float)$item['total_quantity']; ?>
                <td>{{$item['service_name']}}</td>
                <td>{{number_format((float)$item['total_quantity'],2)}}</td>
                <td>{{number_format((float)$item['total_price'],2)}}</td>
                <td>{{number_format((float)$item['avg_price'],2)}}</td>
            </tr>
        @endforeach
        <tr style="page-break-after: always" class="table-head">
            <td><span> Total</span></td>
            <td><span>{{number_format((float)$totalQuantity,2)}}</span></td>
            <td><span>{{number_format((float)$totalPrice,2)}}</span></td>
            <td></td>
        </tr>
        </tbody>
    </table>
</div>
<footer align="center" class="footer">
    <div class="text-center pt-1 w-100" id="footer"><span>Powered by <a
                    href="{{config('sheba.partners_url')}}">sManager</a></span></div>
    <div id="counter">
        <div id="pageCounter">
        </div>
    </div>
</footer>
</body>
</html>
