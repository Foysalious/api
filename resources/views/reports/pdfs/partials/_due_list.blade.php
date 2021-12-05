<table class="table">
    <thead>
    <tr class="table-head">
        <th>No</th>
        <th style="text-align: left">Name</th>
        <th style="text-align: center">Mobile</th>
        <th style="text-align: center">Deposit</th>
        <th style="text-align: center">Due</th>
    </tr>
    </thead>
    <tbody>
    @foreach($list as $key=>$item)
        <tr>
            <td style="width: 8%">{{++$key}}</td>
            <td style="width: 40%">{{$item['customer_name']}}</td>
            <td style="text-align: center; width: 20%">{{$item['customer_mobile']}}</td>
            @if($item['balance_type'] === 'account_receivable')
                <td style="text-align: center;color: #219653; width: 16%">0</td>
                <td style="text-align: center;color: #DC1E1E; width: 16%">{{$item['balance'] }}</td>
            @else
                <td style="text-align: center;color: #219653; width: 16%">{{$item['balance'] }}</td>
                <td style="text-align: center;color: #DC1E1E; width: 16%">0</td>
            @endif
        </tr>
    @endforeach
    <tr>
        <td style="text-align: right" colspan="3">Total</td>
        <td style="text-align: center;color: #219653">{{$stats["deposit"]}}</td>
        <td style="text-align: center;color: #DC1E1E">{{$stats["due"]}}</td>
    </tr>
    </tbody>
</table>