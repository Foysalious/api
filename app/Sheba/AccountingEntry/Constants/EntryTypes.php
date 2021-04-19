<?php namespace App\Sheba\AccountingEntry\Constants;


use Sheba\Helpers\ConstGetter;

class EntryTypes
{
    use ConstGetter;

    const DUE = "due";
    const DEPOSIT = "deposit";
    const INCOME = "income";
    const EXPENSE = "expense";
    const TRANSFER = "transfer";
}