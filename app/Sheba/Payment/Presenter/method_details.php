<?php namespace Sheba\Payment\Presenter;

use Sheba\Payment\Factory\PaymentStrategy;

$wallet = [
    'name' => 'Sheba Credit',
    'asset' => 'sheba_credit',
    'method_name' => 'wallet'
];
$partner_wallet = [
    'name' => 'Sheba Credit',
    'asset' => 'sheba_credit',
    'method_name' => 'wallet'
];
$bkash = [
    'name' => 'bKash',
    'asset' => 'bkash',
    'method_name' => 'bkash'
];
$cbl = [
    'name' => 'City Bank',
    'asset' => 'cbl',
    'method_name' => 'cbl'
];
$online = [
    'name' => 'Other Debit/Credit',
    'asset' => 'ssl',
    'method_name' => 'online'
];
$ok_wallet = [
    'name' => 'Ok Wallet',
    'asset' => 'ok_wallet',
    'method_name' => 'ok_wallet'
];

return [
    PaymentStrategy::WALLET => $wallet,
    PaymentStrategy::PARTNER_WALLET => $partner_wallet,
    PaymentStrategy::BKASH => $bkash,
    PaymentStrategy::CBL => $cbl,
    PaymentStrategy::ONLINE => $online,
    PaymentStrategy::SSL => $online,
    PaymentStrategy::SSL_DONATION => array_merge($online, ['method_name' => 'ssl_donation']),
    PaymentStrategy::PORT_WALLET => $online,
    PaymentStrategy::OK_WALLET => $ok_wallet
];
