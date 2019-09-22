<!DOCTYPE html>
<html lang="en" >
<head>
    <title>Product wise sales report</title>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        @font-face {
            font-family: "Shonar Bangla";
            src: {{storage_path("fonts/Shonar Bangla.ttf")}} format("truetype"); /* IE9*/
            font-weight: bold;
        }

        @font-face {
            font-family: kalpurush;
            src: {{storage_path("fonts/kalpurush.ttf")}} format("truetype");
            font-weight: normal;

        }

        .page-break {
            page-break-after: always;
        }

        body {
            font-family: kalpurush, 'Shonar Bangla', DejaVu Sans, Roboto, sans-serif;
            color: #4a4a4a;
        }

        .heading {
            text-align: center;
            margin-top: 20px;
        }

        .heading h2 {
            font-size: 1.5rem;
            font-weight: bolder;
        }

        .heading .sub-heading {
            font-size: 1rem;
            font-weight: bold;
        }

        .heading .sub-text {
            font-size: .9rem;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th {
            padding: 10px;
        }

        .table-head {
            background-color: #ededed;
        }
    </style>
</head>
<body align="center">
<table style="max-width: 800px;margin: auto;min-width: 600px;font-family: Arial, sans-serif">
    <tbody>
    <tr>
        <td>
            <div class="heading">
                <h2>{{ucfirst($partner->name)}}</h2>
                <h4 class="sub-heading"> পণ্য অনুযায়ী বিক্রয় রিপোর্ট </h4>
                <span class="sub-text">{{convertNumbersToBangla($from->day,false).' '.banglaMonth($from->month).' '.convertNumbersToBangla($from->year,false).' থেকে '.convertNumbersToBangla($to->day,false).' '.banglaMonth($to->month).' '.convertNumbersToBangla($to->year,false)}}</span>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <table class="table table-bordered">
                <thead>
                <tr class="table-head">
                    <th> পণ্যের নাম</th>
                    <th> বিক্রির পরিমাণ</th>
                    <th> বিক্রয়মূল্য</th>
                    <th> গড় বিক্রয়মূল্য</th>
                </tr>
                </thead>
                <tbody>
                <?php $totalQuantity = $totalPrice = 0;?>
                @foreach($data as $item)
                    <tr>
                        <?php $totalPrice += (float)$item['total_price'];
                        $totalQuantity += (float)$item['total_quantity']; ?>
                        <td>{{$item['service_name']}}</td>
                        <td>{{convertNumbersToBangla((float)$item['total_quantity'])}}</td>
                        <td>{{convertNumbersToBangla((float)$item['total_price'])}}</td>
                        <td>{{convertNumbersToBangla((float)$item['avg_price'])}}</td>
                    </tr>
                @endforeach
                <tr class="table-head">
                    <td><span class="font-weight-bold"> মোট</span></td>
                    <td><span class="font-weight-bold">{{convertNumbersToBangla((float)$totalQuantity)}}</span></td>
                    <td><span class="font-weight-bold">{{convertNumbersToBangla((float)$totalPrice)}}</span></td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
    <tfoot>
    <tr>
        <td style="width: 100%;text-align: center">

            <span>Powered By sManager</span>
        </td>
    </tr>
    </tfoot>
</table>
</body>
</html>
