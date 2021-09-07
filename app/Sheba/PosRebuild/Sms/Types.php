<?php namespace App\Sheba\PosRebuild\Sms;

use Sheba\Helpers\ConstGetter;

class Types
{
  use ConstGetter;
  const WEB_STORE_ORDER_SMS = 'WebStoreOrder';
  const ORDER_BILL_SMS = 'OrderBill';
}