<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer report</title>
</head>
<style>

</style>
<body style="width: 595px;    padding-right: 193px; margin: auto">
<?php $gg = Sheba\Helpers\Converters\NumberLanguageConverter::en2bn("");?>


<div style="text-align: center; margin-top: 30px; line-height: 20px;padding-top: 20px;">
    <h4 style="margin: 0; padding: 0; color: #2F3137; font-size: 16px; font-weight: normal;">{{ $data['contact_details']['name'] }} রিপোর্ট</h4>
    <p style="margin: 0; padding: 0">{{ $data['start_date'] }} - {{ $data['end_date'] }}</p>
    <p style="margin: 0; padding: 0; color: #2F3137; font-size: 12px;">রিপোর্ট তৈরীর সময়: {{ $data['now'] }}</p>
</div>
<div style="padding-left:25px; padding-right:25px;">
<div  style="border: 1px solid #E5E5E5; margin-top: 30px;text-align: center">
    <table style="text-align: center;width: 100%">
        @if($data['contact_type'] == 'customer')
        <tr style="text-align: center; padding: 10px 0;padding-bottom: 5px !important;">
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
            <tr style="text-align: center; padding: 10px 0;padding-bottom: 5px !important;">
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

        <hr style="color: #E5E5E5;margin-bottom: 10px !important;">
        @if($data['contact_type'] == 'customer')
            <tr style="text-align: center; border-top: 1px solid #e5e5e5; ">
                <td style=" width: 48%; ">মোট বাকিতে বিক্রয় <span style="color: #BF392B;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['receivable']) }} </span></td>
                <td style=" width: 4%;"><span style="color: #E5E5E5">|</span></td>
                <td style=" width: 48%;">মোট জমা <span style="color: #4FAF61;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['payable']) }}</span></td>

            </tr>
        @elseif($data['contact_type'] == 'supplier')
                <tr style="text-align: center; border-top: 1px solid #e5e5e5; padding: 2px 0;">
                    <td style=" width: 48%; padding: 0 0 16px;">মোট বাকি ক্রয় <span style="color: #4FAF61;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['receivable']) }} </span></td>
                    <td style=" width: 4%;"><span style="color: #E5E5E5">|</span></td>
                    <td style=" width: 48%;padding: 0 0 16px;">মোট পেমেন্ট <span style="color: #BF392B;">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['payable']) }}</span></td>
                </tr>
        @endif
            <tr >
                <td colspan="3"></td>
            </tr>
            <tr >
                <td colspan="3"></td>
            </tr>
    </table>
</div>
</div>
<div style="margin-top: 30px;">
    <?php reset($data['due_list_bn']) ?>
    <p style="margin: 0; padding: 0; padding-left:25px; padding-right:25px; font-size: 12px; color: #868990">লেনদেনের সংখ্যা: {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['due_list_bn'][key($data['due_list_bn'])]['stats']['total_transactions_bn'])  }}  (এই মাস)</p>
    <div style="padding-left:25px; padding-right:25px;text-align: center">
        <?php
            $count =0;
        ?>
        @foreach($data['due_list_bn'] as $key => $value)
            <?php $count++; ?>
            <table style="table-layout: fixed;
        width: 100%;
        border-collapse: collapse; font-size: 12px; margin-bottom: 10px">
                @if($count <= 1)
                    @if($data['contact_type'] == 'customer')
                        <thead>
                        <tr style="background: #F4F5F7; color: #12141A; text-align: left; ">
                            <th style="padding: 9px 16px; width: 150px;text-align: left">তারিখ</th>
                            <th style="text-align: left;">নাম</th>
                            <th style="text-align: right;padding-right: 5px;">বাকি</th>
                            <th style="text-align: right;padding-right: 5px;">জমা</th>
                        </tr>
                        </thead>
                    @elseif($data['contact_type'] == 'supplier')
                        <thead>
                        <tr style="background: #F4F5F7; color: #12141A; text-align: left; ">
                            <th style="padding: 9px 16px; width: 150px;text-align: left">তারিখ</th>
                            <th style="text-align: left;">নাম</th>
                            <th style="text-align: right;padding-right: 5px;">বাকি ক্রয়</th>
                            <th style="text-align: right;padding-right: 5px;">পেমেন্ট</th>
                        </tr>
                        </thead>
                    @endif
                @endif
        <tr style="border: 1px solid #EAECF0;">
            <td style="padding: 9px 16px;">{{ $key }}</td>
            <td>&nbsp;</td>
            <td style="text-align: center; ">&nbsp;</td>
            <td style="text-align: center; ">&nbsp;</td>
        </tr>
        @foreach($value['list'] as $key1 => $v)
            @if($data['contact_type'] == 'customer')
                <tr style="border: 1px solid #EAECF0;">
                    <td style="padding: 9px 16px; width: 150px;text-align: left">{{ $v['entry_at_bn'] }}</td>
                    <td style="text-overflow: ellipsis;">{{ $v['contact_name'] }}</td>
                    @if($v['balance_type'] == 'account_receivable' )
                        <td style="padding-right: 5px;text-align: right;width: 18% ; background: #F9EDEC;color: #BF392B">৳ {{ $v['balance_bn'] }}</td>
                        <td style="padding-right: 5px;text-align: right;width: 18% ;  background: #EFF8F1;">&nbsp;</td>
                    @elseif($v['balance_type'] == 'account_payable' || $v['balance_type'] == 'cleared')
                        <td style="padding-right: 5px;text-align: right;width: 18% ; background: #F9EDEC;">&nbsp;</td>
                        <td style="padding-right: 5px;text-align: right;width: 18% ; background: #EFF8F1;color: #4FAF61">৳ {{ $v['balance_bn'] }}</td>
                    @endif()
                </tr>
            @elseif($data['contact_type'] == 'supplier')
                <tr style="border: 1px solid #EAECF0;">
                    <td style="padding: 9px 16px; width: 150px;text-align: left">{{ $v['entry_at_bn'] }}</td>
                    <td style="text-overflow: ellipsis;">{{ $v['contact_name'] }}</td>
                    @if($v['balance_type'] == 'account_receivable' || $v['balance_type'] == 'cleared' )
                        <td style="padding-right: 5px;text-align: right;width: 18% ; background: #EFF8F1;color: #4FAF61 ">৳ {{ $v['balance_bn'] }}</td>
                        <td style="padding-right: 5px;text-align: right; width: 18% ; background: #F9EDEC;">&nbsp;</td>
                    @elseif($v['balance_type'] == 'account_payable' )
                        <td style="padding-right: 5px;text-align: right; background: #EFF8F1;width: 18% ;">&nbsp;</td>
                        <td style="padding-right: 5px;text-align: right; background: #F9EDEC; color: #BF392B;width: 18% ;">৳ {{ $v['balance_bn'] }}</td>
                    @endif()
                </tr>
            @endif
        @endforeach()
                <tr style="border: 1px solid #EAECF0;">
                    <td style="padding: 9px 16px;">মোট</td>
                    <td>&nbsp;</td>
                    <td style="padding-right: 5px;text-align: right;  color: #bf392b ;">৳ {{ $value['stats']['receivable_bn'] }}</td>
                    <td style="padding-right: 5px;text-align: right; color:#4faf61 ;">৳ {{ $value['stats']['payable_bn'] }}</td>
                </tr>
            <div style="margin-top: 30px;"></div>
            </table>
        @endforeach()

        <!-- Total calculation -->
        <div style="margin-top: 10px"></div>
        <table style="table-layout: fixed;width: 100%;font-size: 12px;border-collapse: collapse;">
            <tr style=" background: #f4f5f7">
                <td style="padding: 9px 16px;">সর্বমোট</td>
                <td ></td>
                <td style="padding-right: 5px;text-align: right; width: 18% ;color: #bf392b">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['receivable']) }}</td>
                <td style="padding-right: 5px;text-align: right; width: 18% ; color: #4faf61">৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['payable']) }}</td>

            </tr>
        </table>
        <div style="margin-top: 10px"></div>
        <table style="table-layout: fixed;width: 100%;border-collapse: collapse;font-size: 12px;">
            @if($data['contact_type'] == 'customer')
                @if($data['stats']['type'] == 'receivable')
                    <tr style=" background: #f9edec">
                        <td style="padding: 9px 16px">মোট ব্যাল্যান্স <span style="color: #868990">(বাকিতে বিক্রয়)</span></td>
                        <!-- <td>&nbsp;</td> -->
                        <td style="text-align: right; background: #f9edec">&nbsp;</td>
                        <td style="padding-right: 5px;text-align: right;font-size: 14px;color: #bf392b;">
                            ৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}
                        </td>
                    </tr>
                @else
                    <tr style=" background: #EFF8F1">
                        <td style="padding: 9px 16px">মোট ব্যাল্যান্স <span style="color: #868990">(জমা আছে)</span></td>
                        <!-- <td>&nbsp;</td> -->
                        <td style="text-align: right; background: #EFF8F1">&nbsp;</td>
                        <td style="padding-right: 5px;text-align: right;font-size: 14px;color: #4faf61;">
                            ৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}
                        </td>
                    </tr>
                @endif
            @elseif($data['contact_type'] == 'supplier')
                @if($data['stats']['type'] == 'receivable')
                    <tr style=" background: #EFF8F1">
                        <td style="padding: 9px 16px">মোট ব্যাল্যান্স <span style="color: #868990">(বাকিতে ক্রয়)</span></td>
                        <!-- <td>&nbsp;</td> -->
                        <td style="text-align: right; background: #EFF8F1">&nbsp;</td>
                        <td style="padding-right: 5px;text-align: right;font-size: 14px;color: #4faf61;">
                            ৳ {{ Sheba\Helpers\Converters\NumberLanguageConverter::en2bn($data['stats']['balance']) }}
                        </td>
                    </tr>
                @else
                    <tr style=" background: #f9edec">
                        <td style="padding: 9px 16px">মোট ব্যাল্যান্স <span style="color: #868990">(অগ্রীম পেমেন্ট)</span></td>
                        <!-- <td>&nbsp;</td> -->
                        <td style="text-align: right; background: #f9edec">&nbsp;</td>
                        <td style="padding-right: 5px;text-align: right;font-size: 14px;color: #bf392b;">
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
