<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <title>Invoice</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="{{resource_path('assets/css/due_tracker_pdf.css')}}">
</head>
<body align="center">
<?php $today = \Carbon\Carbon::today()->format('h:i A');?>
<div>
    <table style="width: 100%">
        <tbody>
        <tr style="border-bottom: 1px solid #ddd">
            <td>
                <table style="width: 100%; @if(isset($method)) padding:10px; @else border-bottom: 1px solid #ddd @endif">
                    @if(isset($payment_receiver))
                        <tr>
                            <td @if(isset($method))style="padding-left: 25px;" width="80" @else width="120" @endif><img
                                        style="max-width: 120px" src="{{$payment_receiver['image']}}" alt=""></td>
                            <td colspan="3">
                                <div style="text-align: left;padding: 10px 0px 0px 50px;">
                                    <div>{{ucfirst($payment_receiver['name'])}}</div><br>
                                    <div style="color: #9b9b9b;margin: 10px 0px">{{$payment_receiver['mobile']}}</div><br>
                                    <div style="color: #9b9b9b;">{{$payment_receiver['address']}}</div>
                                </div>

                            </td>
                            @if(isset($method))
                                <td width="80"><img width="70px" height="70px"
                                                    src="https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/training_video_images/banners/1586691528_tiwnn.png"
                                                    alt="">

                                </td>

                            @endif
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
        @if(isset($pos_order))
            <tr>
                <td>
                    <table style="">
                        <tr>
                            <td colspan="3">
                                @if(isset($user))
                                    <div style="padding: 10px 20px;">
                                        <span style="">Bill to</span><br>
                                        <span style="">{{ucfirst($user['name'])}}</span><br>
                                        <span style="">{{$user['mobile']}}</span><br>
                                        @if(isset($user['address']))
                                        <span style="">{{$user['address']}}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td align="right">
                                <div style="text-align: right">
                            <span style="">
                                <span style="width: 13px">৳</span> {{number_format($amount,2)}}
                            </span><br>
                                    <span>{{$created_at}}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table style="border-bottom: 1px solid #808080;width: 100%;color: #929292" cellspacing="5">
                        <thead>
                        <tr>
                            <th style="color: #9b9b9b">Product Name</th>
                            <th style="color: #9b9b9b">Quantity</th>
                            <th style="color: #9b9b9b">Unit Price</th>
                            <th style="color: #9b9b9b" align="right">Total Price</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $subtotal = 0;?>
                        @foreach($pos_order['items'] as $item)
                            <tr style="border-bottom: 1px solid #808080">
                                <td style="border-bottom: 1px solid #808080">
                                    {{ $item->service_name }} <br>
                                    @if($item->warranty)
                                        <span style="font-size: 10px">* {{ $item->warranty }} {{ $item->warranty_unit }} warranty applied.</span>
                                    @endif
                                </td>

                                <td style="border-bottom: 1px solid #808080">{{$item->quantity}}</td>
                                <td style="border-bottom: 1px solid #808080">
                                <span style="width: 11px">
                                    ৳
                                </span>
                                    {{number_format($item->unit_price,2)}}</td>
                                <?php $total = (double)$item->quantity * (double)$item->unit_price; $subtotal += $total;?>

                                <td align="right" style="border-bottom: 1px solid #808080"><span
                                            style="width: 13px">৳</span> {{number_format($total,2)}}</td>
                            </tr>

                        @endforeach
                        <hr>

                        <tr>
                            <td colspan="3" align="right">Total</td>
                            <td align="right">
                                <span style="width: 11px">৳</span> {{number_format($pos_order['total'],2)}}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right" style="color: #9b9b9b">Total Vat</td>
                            <td align="right" style="color: #383d46"><span
                                        style="width: 13px">৳</span> {{number_format($pos_order['vat'],2)}}</td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right" style="color: #9b9b9b">Total Discount</td>
                            <td align="right" style="color: #383d46"><span
                                        style="width: 13px">৳</span> {{number_format($pos_order['discount'],2)}}</td>
                        </tr>
                        @if(isset($pos_order['delivery_charge']))
                            <tr>
                                <td colspan="3" align="right" style="color: #9b9b9b">Delivery Charge</td>
                                <td align="right" style="color: #383d46"><span
                                            style="width: 13px">৳</span> {{number_format($pos_order['delivery_charge'],2)}}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" align="right" style="color: #9b9b9b">Total Payable</td>
                            <td align="right" style="color: #383d46"><span
                                        style="width: 13px">৳</span> {{number_format($pos_order['grand_total'],2)}}</td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right" style="color: #9b9b9b">Paid</td>
                            <td align="right" style="color: #383d46"><span
                                        style="width: 13px">৳</span> {{number_format($pos_order['paid'],2)}}</td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right" style="color: #9b9b9b">Due</td>
                            <td align="right" style="color: #383d46"><span
                                        style="width: 13px">৳</span> {{number_format($pos_order['due'],2)}}</td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

            <!--<tr>
                <td>
                    <p class="terms">
                        <strong>Terms and Note:</strong>
                        If needed, it can take a maximum of 15 days to get your refund. You will only get return by the way you pay.
                    </p>
                </td>
            </tr>-->

        @endif
    </table>


</div>
</body>
</html>
