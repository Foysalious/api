<!DOCTYPE html>
<html lang="en">
<head>
    <title> Invoice </title>
</head>

<body>
<table align="center" style="max-width: 800px;margin: auto;min-width: 600px;font-family: Arial, sans-serif">
    <tbody>
    <tr style="border-bottom: 1px solid #ddd">
        <td>
            <table style="width: 100%;border-bottom: 1px solid #ddd">
                <tr>
                    <td width="120"><img style="max-width: 120px" src="{{$payment_receiver['image']}}" alt=""></td>
                    <td colspan="3">
                        <div style="text-align: left;padding: 10px 20px;">
                            <span>{{$payment_receiver['name']}}</span><br>
                            <span style="color: #B0BEC5;">{{$payment_receiver['mobile']}}</span><br>
                            <span style="color: #B0BEC5;">{{$payment_receiver['address']}}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>

            <table style="width: 100%;border-bottom: 1px solid #ddd;padding-bottom: 4px">
                <tr>
                    <td colspan="3" style="border-left: 4px solid #1b4280">
                        <div style="padding: 10px 20px">
                            <span style="color: #B0BEC5;">Bill to</span><br>
                            <span style="font-weight: bold;">{{$user['name']}}</span><br>
                            <span style="color: #B0BEC5">{{$user['mobile']}}</span>
                        </div>
                    </td>
                    <td align="right">
                        <div style="text-align: right">
                            <span style="font-weight: bold"> <span style="font-family:Helvetica, sans-serif;">BDT</span> {{number_format($amount,2)}}</span><br>
                            <span style="color: #B0BEC5;">{{$created_at}}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%;color: #636363;" cellpadding="5">
                <tr>
                    <td><i class="fa fa-money"></i></td>
                    <td>Payment amount</td>
                    <td align="right">BDT {{$amount}}</td>
                </tr>
                <tr>
                    <td><i class="fa fa-money"></i></td>
                    <td>Payment purpose</td>
                    <td align="right">{{$description}}</td>
                </tr>
                <tr>
                    <td><i class="fa fa-money"></i></td>
                    <td>Payment time</td>
                    <td align="right">{{$created_at}}</td>
                </tr>
                <tr>
                    <td><i class="fa fa-money"></i></td>
                    <td>Payment type</td>
                    <td align="right">{{$method}}</td>
                </tr>
            </table>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
