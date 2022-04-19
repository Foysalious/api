<div style="background: #e0fbd8; padding: 20px">
    <div>
        <table style="width:100%">
            <tr >
                <td rowspan="2" style="width: 48px;"><img style="max-width: 48px" src="{{$data['data']['partner']['logo'] }}" alt="" /></td>
                <td>{{  $data['data']['partner']['name'] }}</td>
                <td rowspan="2" style="text-align:right;"><img src=" {{ config('constants.smanager_logo') }}" alt=""></td>
            </tr>
            <tr>
                <td >{{ $data['data']['partner']['mobile'] }}</td>
            </tr>
        </table>
    </div>
</div>