<!DOCTYPE html>
<html lang="en">
    <head>
        <title> Topup History Report </title>
    </head>

    <body>
        <table>
            <tr>
                <th>No</th>
                <th>Payee Mobile</th>
                <th>Amount</th>
                <th>Operator</th>
                <th>Status</th>
                <th>Created Date</th>
            </tr>
            @foreach($topup_data as $key => $topup)
                <tr>
                    <td>{{ ++$key }}</td>
                    <td>`{{ $topup['payee_mobile'] }}`</td>
                    <td>{{ $topup['amount'] }}</td>
                    <td>{{ $topup['operator'] }}</td>
                    <td>{{ $topup['status'] }}</td>
                    <td>{{ $topup['created_at'] }}</td>
                </tr>
            @endforeach
        </table>
    </body>
</html>
