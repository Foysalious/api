<table class="order-info" style="border-bottom:1px solid #666;">
    <tr>
        <td style="width:130px;">Order Number</td>
        <td>: {{ $partner_order->code() }}</td>
        <td> </td>
        <td style="width:130px; text-align:left;">Customer Name</td>
        <td>:  {{ $partner_order->order->delivery_name }}</td>
    </tr>
    <tr>
        <td style="width:130px;"> Order Date</td>
        <td>: {{ $partner_order->order->created_at->format('d M, Y h:i A') }}</td>
        <td> </td>
        <td style="width:130px; text-align:left;">Customer Phone</td>
        <td>:  {{ $partner_order->order->delivery_mobile }}</td>
    </tr>
    <tr>
        <td style="width:130px;">SP Order Statement No</td>
        <td>: {{ $partner_order->id }}</td>
        <td> </td>
        <td style="width:130px; text-align:left;">Customer Address</td>
        <td>:  {{ $partner_order->order->delivery_address }}</td>
    </tr>
    <tr>
        <td>Date</td>
        <td>: {{ \Carbon\Carbon::now()->format('d M, Y h:i A') }}</td>
        <td> </td>
        <td style="width:130px; text-align:left;">Service Provider Name</td>
        <td style="width:200px; text-align:left;">: {{ $partner_order->partner ? $partner_order->partner->name : '' }}</td>

    </tr>
</table>
<br>

{{--

<table style="border-bottom:1px solid #666;">
    <tr>
        <td style="width:130px;">Client Name</td>
        <td>:  {{ $partner_order->order->delivery_name }}</td>
    </tr>
    <tr>
        <td>Mobile</td>
        <td>:  {{ $partner_order->order->delivery_mobile }}</td>
    </tr>
    <tr>
        <td>Address</td>
        <td>:  {{ $partner_order->order->delivery_address }}</td>
    </tr>

</table>
<br>

--}}
