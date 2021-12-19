<thead>
    <tr>
        <th class="code">JOB CODE</th>
        <th class="">NAME</th>
        <th class="qty" style="background-color: #DDDDDD;">QTY</th>
        <th class="qty">UNIT PRICE</th>
        <th class="unit">PRICE</th>
        <th class="qty">MATERIAL</th>
        <th class="qty" style="background-color: #DDDDDD;">DISCOUNT</th>
        <th class="total">TOTAL</th>
    </tr>
</thead>

<tbody>
@forelse($partner_order->jobs as $job)
    @if($job->status != "Cancelled")
        <tr>
            <td class="no"><b>{{ $job->code() }}</b></td>
            <td class="">
                <h3>{{ $job->service_name }}</h3>
                <span>
                    @foreach(json_decode($job->service_variables ) as $key => $serviceVariable)
                        {{ ($key != 0 ? "," : "") }}{{ $serviceVariable->answer }}
                    @endforeach
                </span>
            </td>
            <td class="qty" style="background-color: #DDDDDD;"> {{ $job->service_quantity }} </td>
            <td class="qty"> {{ $job->service_unit_price }} </td>
            <td class="unit"> {{ $job->servicePrice }} </td>
            <td class="qty"> {{ $job->materialPrice }} </td>
            <td class="qty" style="background-color: #DDDDDD;"> {{ $job->discount }} </td>
            <td class="total"> {{ $job->grossPrice }} </td>
        </tr>
    @endif
@empty
    <tr>
        <td class="text-center" colspan="8"> No Jobs Found.</td>
    </tr>
@endforelse
</tbody>

<tfoot>
    <tr>
        <td colspan="4"></td>
        <td class="text-left" colspan="3">SUBTOTAL</td>
        <td class="s-price">{{ $partner_order->totalPrice }}</td>
    </tr>

    @if($partner_order->discount>0)
        <tr>
            <td colspan="4"></td>
            <td class="text-left" colspan="3">DISCOUNT</td>
            <td class="s-price">{{ $partner_order->discount }}</td>
        </tr>
    @endif

    @if($partner_order->roundingCutOff>0)
        <tr>
            <td colspan="4"></td>
            <td class="text-left" colspan="3">ROUNDING CUT OFF</td>
            <td class="s-price">{{ $partner_order->roundingCutOff }}</td>
        </tr>
    @endif

    @if($partner_order->due>0 && $type !== "QUOTATION")
        <tr>
            <td colspan="4"></td>
            <td class="text-left" colspan="3">PAID</td>
            <td class="s-price">{{ $partner_order->paid }}</td>
        </tr>
    @endif

    <tr>
        <td colspan="4"></td>
        @if($partner_order->due>0 && $type !== "QUOTATION")
            <td class="text-left" colspan="3">DUE AMOUNT</td>
            <td class="s-price">{{ $partner_order->due }}</td>
        @else
            <td class="text-left" colspan="3">GRAND TOTAL @if($type=="QUOTATION") @endif</td>
            <td class="s-price">{{ $partner_order->grossAmount }}</td>
        @endif
    </tr>
</tfoot>