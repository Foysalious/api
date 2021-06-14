<?php $job = $partner_order->order->lastJob()->calculate(true); ?>

<thead>
    <tr>
        <th class="desc">SERVICE NAME</th>
        <th class="qty" style="background-color: #DDDDDD;">QTY</th>
        <th class="qty">UNIT PRICE</th>
        <th class="unit">PRICE</th>
    </tr>
</thead>

<tbody>
@if($job->status != "Cancelled")
    @forelse($job->jobServices as $service)
        <tr>
            <td class="">
                <h3>{{ $service->name }}</h3>
                <span>
                    @foreach(json_decode($service->variables ) as $key => $serviceVariable)
                        {{ ($key != 0 ? "," : "") }}{{ $serviceVariable->answer }}
                    @endforeach
                </span>
            </td>
            <td class="qty" style="background-color: #DDDDDD;"> {{ $service->quantity }} </td>
            <td class="qty"> {{ $service->unit_price }} </td>
            <td class="unit">
                {{ $service->unit_price * $service->quantity }} <br>
                @if($service->min_price)
                    <span style="color: #f20631;">(Minimum Price Applied)</span>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td class="text-center" colspan="6"> No Service Found.</td>
        </tr>
    @endforelse
@else
    <tr>
        <td class="text-center" colspan="6"> Sorry, Job Has been cancelled. </td>
    </tr>
@endif
</tbody>

<tfoot>
    <tr>
        <td colspan="2"></td>
        <td class="text-left" colspan="1">TOTAL SERVICE PRICE</td>
        <td class="s-price">{{ $job->servicePrice }}</td>
    </tr>

    @if($job->materialPrice>0)
    <tr>
        <td colspan="2"></td>
        <td class="text-left" colspan="1">TOTAL MATERIAL PRICE</td>
        <td class="s-price">{{ $job->materialPrice }}</td>
    </tr>
    @endif

    @if($partner_order->totalDiscount > 0)
        <tr>
            <td colspan="2"></td>
            <td class="text-left" colspan="1">DISCOUNT</td>
            <td class="s-price">{{ $partner_order->totalDiscount }}</td>
        </tr>
    @endif

    @if(!$job->first_logistic_order_id && !$job->last_logistic_order_id && $job->site == 'customer')
        <tr>
            <td colspan="2"></td>
            <td class="text-left" colspan="1">DELIVERY CHARGE</td>
            <td class="s-price">{{ $job->deliveryPrice }}</td>
        </tr>
    @elseif($job->logistic_charge)
        <tr>
            <td colspan="2"></td>
            <td class="text-left" colspan="1">LOGISTIC CHARGE</td>
            <td class="s-price">{{ number_format($partner_order->totalLogisticCharge, 2) }}</td>
        </tr>
    @endif

    <tr>
        <td colspan="2"></td>
        <td class="text-left" colspan="1">SUBTOTAL</td>
        <td class="s-price">{{ number_format($partner_order->totalPrice + $partner_order->totalLogisticCharge, 2) }}</td>
    </tr>

    @if($partner_order->roundingCutOff>0)
        <tr>
            <td colspan="2"></td>
            <td class="text-left" colspan="1">ROUNDING CUT OFF</td>
            <td class="s-price">{{ $partner_order->roundingCutOff }}</td>
        </tr>
    @endif

    @if($partner_order->due>0 && $type !== "QUOTATION")
        <tr>
            <td colspan="2"></td>
            <td class="text-left" colspan="1">PAID</td>
            <td class="s-price">{{ $partner_order->paid }}</td>
        </tr>
    @endif

    <tr>
        <td colspan="2"></td>
        <td class="text-left" colspan="1">VAT</td>
        <td class="s-price">{{ $partner_order->vat }}</td>
    </tr>

    <tr>
        <td colspan="2"></td>
        @if($partner_order->due>0 && $type !== "QUOTATION")
            <td class="text-left" colspan="1">DUE AMOUNT @if($type=="QUOTATION") *** @endif</td>
            <td class="s-price">{{ $partner_order->dueWithLogistic }}</td>
        @else
            <td class="text-left" colspan="1">GRAND TOTAL @if($type=="QUOTATION") @endif</td>
            <td class="s-price">{{ number_format($partner_order->grossAmountWithLogistic, 2) }}</td>
        @endif
    </tr>
</tfoot>
