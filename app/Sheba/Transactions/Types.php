<?php namespace Sheba\Transactions;


use Sheba\Helpers\ConstGetter;

class Types
{
    use ConstGetter;

    const DEBIT = "Debit";
    const CREDIT = "Credit";
    static function debit(){
        return strtolower(self::DEBIT);
    }
    static function credit(){
        return strtolower(self::CREDIT);
    }
}
