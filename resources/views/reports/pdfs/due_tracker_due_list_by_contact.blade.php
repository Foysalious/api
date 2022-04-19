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
        @if($data['contact_type'] == 'customer')
        <tr style="text-align: center; padding: 10px 0;">
            @if($data['stats']['type'] == 'receivable')
                <td style="text-align: center; padding: 16px;" colspan="3">ব্যালেন্স (বাকিতে বিক্রয়)
                    <span style="color: #BF392B;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}</span>
                </td>
            @else
                <td style="text-align: center; padding: 16px;" colspan="3">ব্যাল্যান্স (জমা আছে)
                    <span style="color: #4faf61;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}</span>
                </td>
            @endif
        </tr>
        @elseif($data['contact_type'] == 'supplier')
            <tr style="text-align: center; padding: 10px 0;">
                @if($data['stats']['type'] == 'receivable')
                    <td style="text-align: center; padding: 16px;" colspan="3">ব্যালেন্স (বাকিতে ক্রয়)
                        <span style="color: #4faf61;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}</span>
                    </td>
                @else
                    <td style="text-align: center; padding: 16px;" colspan="3">ব্যাল্যান্স (অগ্রিম পেমেন্ট)
                        <span style="color: #BF392B;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}</span>
                    </td>
                @endif
            </tr>
        @endif

        <hr style="color: #E5E5E5">
        @if($data['contact_type'] == 'customer')
            <tr style="text-align: center; border-top: 1px solid #e5e5e5; padding: 10px 0;">
                <td style=" width: 48%; padding: 0 0 16px;">মোট বাকিতে বিক্রয় <span style="color: #BF392B;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['receivable']) }} </span></td>
                <td style=" width: 4%;"><span style="color: #E5E5E5">|</span></td>
                <td style=" width: 48%;padding: 0 0 16px;">মোট জমা <span style="color: #4FAF61;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['payable']) }}</span></td>
            </tr>
        @elseif($data['contact_type'] == 'supplier')
                <tr style="text-align: center; border-top: 1px solid #e5e5e5; padding: 10px 0;">
                    <td style=" width: 48%; padding: 0 0 16px;">মোট বাকি ক্রয় <span style="color: #4FAF61;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['receivable']) }} </span></td>
                    <td style=" width: 4%;"><span style="color: #E5E5E5">|</span></td>
                    <td style=" width: 48%;padding: 0 0 16px;">মোট পেমেন্ট <span style="color: #BF392B;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['payable']) }}</span></td>
                </tr>
        @endif
    </table>
</div>

<div style="margin-top: 30px;">
    <?php reset($data['due_list_bn']) ?>
    <p style="margin: 0; padding: 0; font-size: 12px; ">লেনদেনের সংখ্যা: {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['due_list_bn'][key($data['due_list_bn'])]['stats']['total_transactions_bn'])  }}  (এই মাস)</p>
    <div style="border: 1px solid #E5E5E5; margin-top: 30px;text-align: center">
    <table style="table-layout: fixed;
        width: 100%;
        border-collapse: collapse; font-size: 12px;">
        @if($data['contact_type'] == 'customer')
            <thead>
            <tr style="background: #F4F5F7; color: #12141A; text-align: left; ">
                <th style="padding: 9px 16px; width: 150px">তারিখ</th>
                <th style="text-align: left;">বিস্তারিত</th>
                <th style="text-align: right;">বাকি</th>
                <th style="text-align: right;">জমা</th>
                <th style="text-align: right; padding-right: 16px;">ব্যাল্যান্স</th>
            </tr>
            </thead>
        @elseif($data['contact_type'] == 'supplier')
            <thead>
            <tr style="background: #F4F5F7; color: #12141A; text-align: left; ">
                <th style="padding: 9px 16px; width: 150px">তারিখ</th>
                <th style="text-align: left;">বিস্তারিত</th>
                <th style="text-align: right;">বাকি ক্রয়</th>
                <th style="text-align: right;">পেমেন্ট</th>
                <th style="text-align: right; padding-right: 16px;">ব্যাল্যান্স</th>
            </tr>
            </thead>
        @endif
        @foreach($data['due_list_bn'] as $key => $value)
        <tr style="border: 1px solid #EAECF0;">
            <td style="padding: 9px 16px;">{{ $key }}</td>
            <td>&nbsp;</td>
            <td style="text-align: center; ">&nbsp;</td>
            <td style="text-align: center; ">&nbsp;</td>
            <td style="text-align: right; padding-right: 16px;">&nbsp;</td>
        </tr>
        @foreach($value['list'] as $key1 => $v)
            @if($data['contact_type'] == 'customer')
                <tr style="border: 1px solid #EAECF0;">
                    <td style="padding: 9px 16px;text-align: left;">{{ $v['entry_at_bn'] }}</td>
                    <td>{{ $v['note'] }}</td>
                    @if($v['account_type'] == 'receivable' )
                        <td style="text-align: right; background: #F9EDEC;color: #C92236">৳ {{ $v['amount_bn'] }}</td>
                        <td style="text-align: right;  background: #EFF8F1;">&nbsp;</td>
                        <td style="text-align: right; color: #C92236; font-size: 14px;">৳ {{$v['balance_bn']}}</td>
                    @elseif($v['account_type'] == 'payable' )
                        <td style="text-align: right; background: #F9EDEC;">&nbsp;</td>
                        <td style="text-align: right; background: #EFF8F1; color: #39B54A">৳ {{ $v['amount_bn'] }}</td>
                        <td style="text-align: right; color: #39B54A; font-size: 14px;">৳ {{$v['balance_bn']}}</td>
                    @endif()

                </tr>
            @elseif($data['contact_type'] == 'supplier')
                <tr style="border: 1px solid #EAECF0;">
                    <td style="padding: 9px 16px;text-align: left;">{{ $v['entry_at_bn'] }}</td>
                    <td>{{ $v['note'] }}</td>
                    @if($v['account_type'] == 'receivable' )
                        <td style="text-align: right; background: #EFF8F1; color: #39B54A">৳ {{ $v['amount_bn'] }}</td>
                        <td style="text-align: right;  background: #F9EDEC;">&nbsp;</td>
                        <td style="text-align: right; color: #39B54A; font-size: 14px;">৳ {{$v['balance_bn']}}</td>
                    @elseif($v['account_type'] == 'payable' )
                        <td style="text-align: right; background: #EFF8F1;">&nbsp;</td>
                        <td style="text-align: right; background: #F9EDEC; color: #C92236">৳ {{ $v['amount_bn'] }}</td>
                        <td style="text-align: right; color: #C92236; font-size: 14px;">৳ {{$v['balance_bn']}}</td>
                    @endif()
                </tr>
            @endif
        @endforeach()

            <div style="margin-top: 30px;"></div>
        @endforeach()
    </table>
        <!-- Total calculation -->
        <div style="margin-top: 10px"></div>
        <table style="table-layout: fixed;border-collapse: collapse; width: 100%;font-size: 12px;">
            <tr style=" background: #f4f5f7">
                <td style="padding: 9px 16px;width: 20% ;">সর্বমোট</td>
                <td style="width: 25% "></td>
                <td style="text-align: right; width: 18% ;color: #bf392b">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['receivable']) }}</td>
                <td style="text-align: right; width: 18% ; color: #4faf61">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['payable']) }}</td>
                <td style="text-align: right;  font-size: 14px; color: #4faf61;">
                    ৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}
                </td>
            </tr>
        </table>
        <div style="margin-top: 10px"></div>
        <table style="table-layout: fixed;width: 100%;border-collapse: collapse;font-size: 12px;">
            @if($data['contact_type'] == 'customer')
                @if($data['stats']['type'] == 'receivable')
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
            @elseif($data['contact_type'] == 'supplier')
                @if($data['stats']['type'] == 'receivable')
                    <tr style=" background: #EFF8F1">
                        <td style="padding: 9px 16px">মোট ব্যাল্যান্স (বাকিতে ক্রয়)</td>
                        <!-- <td>&nbsp;</td> -->
                        <td style="text-align: right; background: #EFF8F1">&nbsp;</td>
                        <td style="text-align: right;font-size: 14px;color: #4faf61;">
                            ৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}
                        </td>
                    </tr>
                @else
                    <tr style=" background: #f9edec">
                        <td style="padding: 9px 16px">মোট ব্যাল্যান্স (অগ্রীম পেমেন্ট)</td>
                        <!-- <td>&nbsp;</td> -->
                        <td style="text-align: right; background: #f9edec">&nbsp;</td>
                        <td style="text-align: right;font-size: 14px;color: #bf392b;">
                            ৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}
                        </td>
                    </tr>
                @endif
            @endif
        </table>
    </div>

    </div>



</body>
</html>
