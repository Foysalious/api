<table class="table table-bordered">
    <thead>
    <tr class="table-head">
        <th>No</th>
        <th style="text-align: center">Date</th>
        <th style="text-align: center">Description</th>
        <th style="text-align: center">Credit</th>
        <th style="text-align: center">Debit</th>
    </tr>
    </thead>
    <tbody>
    @foreach($list as $key=>$item)
        <tr>
            <td style="width: 9%">{{++$key}}</td>
            <td style="width: 15%">{{date('d-m-Y', strtotime($item['created_at'])) }}</td>

            @if($item['source_type'] === 'PosOrder')
                <td style="text-align: center; width: 50%">Purchase, Order Id #{{$item['partner_wise_order_id']}}</td>
            @elseif($item['head'] === 'Due Tracker')
                @if($item['note'])
                    <td style="text-align: center; width: 50%"> {{$item['note']}} </td>
                @else
                    <td style="text-align: center; width: 50%"> -- </td>
                @endif
            @else
                <td style="text-align: center; width: 50%">{{$item['head']}}</td>
            @endif
            @if($item['type'] === 'deposit')
                <td style="text-align: center;color: #219653;width: 13%">0</td>
                <td style="text-align: center;color: #DC1E1E;width: 13%">{{$item['amount'] }}</td>
            @else
                <td style="text-align: center;color: #219653;width: 13%">{{$item['amount'] }}</td>
                <td style="text-align: center;color: #DC1E1E;width: 13%">0</td>
            @endif
        </tr>
    @endforeach
    <tr>
        <td style="text-align: right" colspan="3">Total</td>
        <td style="text-align: center;color: #219653">{{$other_info["total_credit"]}}</td>
        <td style="text-align: center;color: #DC1E1E">{{$other_info["total_debit"]}}</td>
    </tr>
    </tbody>
</table>