<?php $items = $order->items; ?>

<thead>
<tr>
    <th class="desc">SERVICE NAME</th>
    <th class="qty" style="background-color: #DDDDDD;">QTY</th>
    <th class="qty">UNIT PRICE</th>
    <th class="unit">PRICE</th>
</tr>
</thead>

<tbody>
@forelse($items as $item)
    <?php $item = $item->calculate(); ?>
    <tr>
        <td class=""> <h3>{{ $item->service_name }}</h3> </td>
        <td class="qty" style="background-color: #DDDDDD;"> {{ $item->quantity }} </td>
        <td class="qty"> {{ $item->unit_price }} </td>
        <td class="unit"> {{ $item->getTotal() }} </td>
    </tr>
@empty
    <tr>
        <td class="text-center" colspan="4"> No Service Found.</td>
    </tr>
@endforelse
</tbody>

<tfoot>
<tr>
    <td colspan="2"></td>
    <td class="text-left" colspan="1">TOTAL SERVICE PRICE</td>
    <td class="s-price">{{ $order->getTotalBill() }}</td>
</tr>

@if($order->getAppliedDiscount() > 0)
    <tr>
        <td colspan="2"></td>
        <td class="text-left" colspan="1">DISCOUNT</td>
        <td class="s-price">{{ $order->getAppliedDiscount() }}</td>
    </tr>
@endif

<tr>
    <td colspan="2"></td>
    <td class="text-left" colspan="1">SUBTOTAL</td>
    <td class="s-price">{{ $order->getNetBill() }}</td>
</tr>

<tr>
    <td colspan="2"></td>
    <td class="text-left" colspan="1">PAID AMOUNT</td>
    <td class="s-price">{{ $order->getPaid() }}</td>
</tr>

<tr>
    <td colspan="2"></td>
    <td class="text-left" colspan="1">DUE AMOUNT</td>
    <td class="s-price">{{ $order->getDue() }}</td>
</tr>
</tfoot>