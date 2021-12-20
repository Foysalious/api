<?php namespace Sheba\ExpenseTracker;

use Exception;
use Sheba\Helpers\ConstGetter;

class EntryType
{
    use ConstGetter;

    const INCOME    = 'income';
    const EXPENSE   = 'expense';
    const PARTNER = 'partner';

    /**
     * @param $status
     * @return string
     * @throws Exception
     */
    public static function getRoutable($status)
    {
        switch ($status) {
            case self::INCOME: return 'incomes';
            case self::EXPENSE: return 'expenses';
            default: throw new Exception('Invalid Type Exception');
        }
    }
}
