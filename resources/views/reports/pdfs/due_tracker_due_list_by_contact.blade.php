<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer report</title>
</head>
<style>

</style>
<body style="width: 595px; margin: auto">
<?php $gg = Sheba\Helpers\Converters\NumberLanguageConverter::en2bn("");?>


<div style="text-align: center; margin-top: 30px; line-height: 20px;">
    <h4 style="margin: 0; padding: 0; color: #2F3137; font-size: 16px; font-weight: normal;">{{ $data['contact_details']['name'] }} এর রিপোর্ট</h4>
    <p style="margin: 0; padding: 0">{{ $data['start_date'] }} - {{ $data['end_date'] }}</p>
    <p style="margin: 0; padding: 0; color: #2F3137; font-size: 12px;">রিপোর্ট তৈরীর সময়: {{ $data['now'] }}</p>
</div>

<div style="border: 1px solid #E5E5E5; margin-top: 30px;text-align: center">
    <table style="text-align: center;width: 100%">
        <tr style="text-align: center; padding: 10px 0;">
            @if($data['stats']['type'] = 'receivable')
            <td style="text-align: center; padding: 16px;" colspan="3">ব্যালেন্স (বাকিতে বিক্রয়)
                    <span style="color: #BF392B;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}</span>
                @else
                 <td style="text-align: center; padding: 16px;" colspan="3">ব্যাল্যান্স (জমা আছে)
                    <span style="color: #4faf61;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}</span>
                @endif
                </td>
        </tr>
        <hr style="color: #E5E5E5">
        <tr style="text-align: center; border-top: 1px solid #e5e5e5; padding: 10px 0;">
            <td style=" width: 48%; padding: 0 0 16px;">মোট বাকিতে বিক্রয় <span style="color: #BF392B;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['receivable']) }} </span></td>
            <td style=" width: 4%;"><span style="color: #E5E5E5">|</span></td>
            <td style=" width: 48%;padding: 0 0 16px;">মোট জমা <span style="color: #4FAF61;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['payable']) }}</span></td>
        </tr>
    </table>
</div>

<div style="margin-top: 30px;">
    <?php reset($data['due_list_bn']) ?>
    <p style="margin: 0; padding: 0; font-size: 12px; ">লেনদেনের সংখ্যা: {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['due_list_bn'][key($data['due_list_bn'])]['stats']['total_transactions_bn'])  }}  (এই মাস)</p>
    <div style="border: 1px solid #E5E5E5; margin-top: 30px;text-align: center">
    <table style="table-layout: fixed;
        width: 100%;
        border-collapse: collapse; font-size: 12px;">
        <thead>
        <tr style="background: #F4F5F7; color: #12141A; text-align: left; ">
            <th style="padding: 9px 16px; width: 150px">তারিখ</th>
            <th style="text-align: left;">বিস্তারিত</th>
            <th style="text-align: right;">বাকি</th>
            <th style="text-align: right;">জমা</th>
            <th style="text-align: right; padding-right: 16px;">ব্যাল্যান্স</th>
        </tr>
        </thead>
        @foreach($data['due_list_bn'] as $key => $value)
        <tr style="border: 1px solid #EAECF0;">
            <td style="padding: 9px 16px;">{{ $key }}</td>
            <td>&nbsp;</td>
            <td style="text-align: center; ">&nbsp;</td>
            <td style="text-align: center; ">&nbsp;</td>
            <td style="text-align: right; padding-right: 16px;">&nbsp;</td>
        </tr>
        @foreach($value['list'] as $key1 => $v)
        <tr style="border: 1px solid #EAECF0;">
            <td style="padding: 9px 16px;text-align: left;">{{ $v['entry_at_bn'] }}</td>
            <td>{{ $v['note'] }}</td>
            @if($v['account_type'] == 'receivable' )
                <td style="text-align: right; background: #F9EDEC;">৳ {{ $v['amount_bn'] }}</td>
                <td style="text-align: right;  background: #EFF8F1;">&nbsp;</td>
            @elseif($v['account_type'] == 'payable' )
                <td style="text-align: right; background: #F9EDEC;">&nbsp;</td>
                <td style="text-align: right; background: #EFF8F1; ">৳ {{ $v['amount_bn'] }}</td>
            @endif()
            <td style="text-align: right; color: #BF392B; font-size: 14px;">৳ {{$v['balance_bn']}}</td>
        </tr>
{{--                <hr style="color: #E5E5E5; width: fit-content">--}}
        @endforeach()

            <div style="margin-top: 30px;"></div>
        @endforeach()
    </table>
        <!-- Total calculation -->
        <div style="margin-top: 10px"></div>
        <table style="table-layout: fixed;width: 100%;border-collapse: collapse;font-size: 12px;">
            <tr style=" background: #f4f5f7">
                <td style="padding: 9px 16px;width: 20% ;">সর্বমোট</td>
                <td style="width: 22% "></td>
                <td style="text-align: right; width: 18% ;color: #bf392b">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['receivable']) }}</td>
                <td style="text-align: right; width: 18% ; color: #4faf61">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['payable']) }}</td>
                <td style="text-align: right;  font-size: 14px; color: #4faf61;">
                    ৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}
                </td>
            </tr>
        </table>
        <div style="margin-top: 10px"></div>
        <table style="table-layout: fixed;width: 100%;border-collapse: collapse;font-size: 12px;">
            @if($data['stats']['type'] = 'receivable')
                <tr style=" background: #f9edec">
                    <td style="padding: 9px 16px">মোট ব্যাল্যান্স (বাকিতে বিক্রয়)</td>
                    <!-- <td>&nbsp;</td> -->
                    <td style="text-align: right; background: #f9edec">&nbsp;</td>
                    <td style="text-align: right;font-size: 14px;color: #bf392b;">
                        ৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}
                    </td>
                </tr>
            @else
                <tr style=" background: #EFF8F1">
                    <td style="padding: 9px 16px">মোট ব্যাল্যান্স (জমা আছে)</td>
                    <!-- <td>&nbsp;</td> -->
                    <td style="text-align: right; background: #EFF8F1">&nbsp;</td>
                    <td style="text-align: right;font-size: 14px;color: #4faf61;">
                        ৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}
                    </td>
                </tr>
            @endif
        </table>
    </div>

    </div>



</body>
</html>
