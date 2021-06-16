<?php namespace Sheba\Payment\Presenter;

use Sheba\Payment\Factory\PaymentStrategy;

$wallet         = [
    'name'           => 'Sheba Credit',
    'name_bn'        => null,
    'asset'          => 'sheba_credit',
    'method_name'    => 'wallet',
    'icon'           => null,
    'cash_in_charge' => 0,
];
$partner_wallet = [
    'name'           => 'Sheba Credit',
    'name_bn'        => null,
    'asset'          => 'sheba_credit',
    'method_name'    => 'wallet',
    'icon'           => null,
    'cash_in_charge' => 0,
];
$bkash          = [
    'name'           => 'bKash',
    'name_bn'        => 'বিকাশ',
    'asset'          => 'bkash',
    'method_name'    => 'bkash',
    'icon'           => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/payments/Bkash.png',
    'cash_in_charge' => 0,
];
$cbl            = [
    'name'           => 'City Bank',
    'name_bn'        => null,
    'asset'          => 'cbl',
    'method_name'    => 'cbl',
    'icon'           => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/payments/City+Bank.png',
    'cash_in_charge' => 0,
];
$online         = [
    'name'           => 'Other Debit/Credit',
    'name_bn'        => null,
    'asset'          => 'ssl',
    'method_name'    => 'online',
    'icon'           => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/payments/Visa+Master.png',
    'cash_in_charge' => 0,
];
$ok_wallet      = [
    'name'           => 'Ok Wallet',
    'name_bn'        => null,
    'asset'          => 'ok_wallet',
    'method_name'    => 'ok_wallet',
    'icon'           => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/payments/OK+Wallet.png',
    'cash_in_charge' => 0,
];
$nagad          = [
    'name'           => 'Nagad',
    'name_bn'        => 'নগদ',
    'asset'          => 'nagad',
    'method_name'    => 'nagad',
    'icon'           => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/payments/Nagad.png',
    'cash_in_charge' => 0,
];
$ebl            = [
    'name'           => 'Eastern Bank Limited',
    'name_bn'        => null,
    'asset'          => 'ebl',
    'method_name'    => 'ebl',
    'icon'           => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/payments/Nagad.png',
    'cash_in_charge' => 0
];

return [
    PaymentStrategy::WALLET         => $wallet,
    PaymentStrategy::PARTNER_WALLET => $partner_wallet,
    PaymentStrategy::BKASH          => $bkash,
    PaymentStrategy::CBL            => $cbl,
    PaymentStrategy::ONLINE         => $online,
    PaymentStrategy::SSL            => $online,
    PaymentStrategy::SSL_DONATION   => array_merge($online, ['method_name' => 'ssl_donation']),
    PaymentStrategy::PORT_WALLET    => $online,
    PaymentStrategy::OK_WALLET      => $ok_wallet,
    PaymentStrategy::NAGAD          => $nagad,
    PaymentStrategy::EBL            => $ebl
];
