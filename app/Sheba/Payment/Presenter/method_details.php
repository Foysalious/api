<?php namespace Sheba\Payment\Presenter;

use Sheba\Payment\Factory\PaymentStrategy;

$wallet         = [
    'name'        => 'Sheba Credit',
    'name_bn'     =>  null,
    'asset'       => 'sheba_credit',
    'method_name' => 'wallet'
];
$partner_wallet = [
    'name'        => 'Sheba Credit',
    'name_bn'     =>  null,
    'asset'       => 'sheba_credit',
    'method_name' => 'wallet'
];
$bkash          = [
    'name'        => 'bKash',
    'name_bn'     => 'বিকাশ',
    'asset'       => 'bkash',
    'method_name' => 'bkash'
];
$cbl            = [
    'name'        => 'City Bank',
    'name_bn'     =>  null,
    'asset'       => 'cbl',
    'method_name' => 'cbl'
];
$online         = [
    'name'        => 'Other Debit/Credit',
    'name_bn'     =>  null,
    'asset'       => 'ssl',
    'method_name' => 'online'
];
$ok_wallet      = [
    'name'        => 'Ok Wallet',
    'name_bn'     =>  null,
    'asset'       => 'ok_wallet',
    'method_name' => 'ok_wallet'
];
$nagad          = [
    'name'        => 'Nagad',
    'name_bn'     => 'নগদ',
    'asset'       => 'nagad',
    'method_name' => 'nagad'
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
    PaymentStrategy::NAGAD          => $nagad
];
