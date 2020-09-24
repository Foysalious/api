<?php namespace Sheba\EMI;

class CalculatorForManager extends Calculator
{
    public function getInterestRatesBreakDowns()
    {
        return config('emi.manager.breakdowns');
    }

    public function getBankFeePercentage()
    {
        return config('emi.manager.bank_fee_percentage');
    }
}
