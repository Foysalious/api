<?php namespace Sheba\FraudDetection;

use Sheba\Helpers\ConstGetter;

class RuleType
{
    use ConstGetter;

    const SHEBA_WALLET = 'sheba_wallet';
    const INDIVIDUAL_WALLET = 'individual_wallet';
}