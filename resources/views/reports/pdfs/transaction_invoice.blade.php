<!DOCTYPE html>
<html lang="en">
<head>
    <title> Invoice </title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
            <table style="width: 100%;color: #636363;" class="invoice-table" cellpadding="5">
                <tr>
                    <td><i class="material-icons">account_balance_wallet</i></td>
                    <td>Payment amount</td>
                    <td align="right">BDT {{$amount}}</td>
                </tr>
                <tr>
                    <td><i class="material-icons">info</i></td>
                    <td>Payment purpose</td>
                    <td align="right">{{$description}}</td>
                </tr>
                <tr>
                    <td><i class="material-icons">event_available</i></td>
                    <td>Payment time</td>
                    <td align="right">{{$created_at}}</td>
                </tr>
                <tr>
                    <td><i class="material-icons">style</i></td>
                    <td>Payment type</td>
                    <td align="right">{{$method}}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <p class="terms"><strong>Terms and Note:</strong> If needed, it can take a maximum of 15 days to get your refund. You will only get return by the way you pay.</p>
        </td>
    </tr>
    </tbody>
</table>

<style>
    .invoice-table tr {
        color: #689ab8;
        font-size: 14px;
    }
    .invoice-table tr i {
        font-size: 14px;
        position: relative;
        top: 2px
    }
    .terms {
        font-size: 12px;
        padding-top: 20px;
        border-top: 1px solid #e1e1e1;
        color: #4a4a4a;
    }
</style>
</body>
</html>
