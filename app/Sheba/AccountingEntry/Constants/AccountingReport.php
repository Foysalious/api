<?php namespace App\Sheba\AccountingEntry\Constants;

use Sheba\Helpers\ConstGetter;

class AccountingReport
{
    use ConstGetter;

    const PROFIT_LOSS_REPORT = 'profit_loss_report';
    const JOURNAL_REPORT = 'journal_report';
    const BALANCE_SHEET_REPORT = 'balance_sheet_report';
    const GENERAL_LEDGER_REPORT = 'general_ledger_report';
    const DETAILS_LEDGER_REPORT = 'details_ledger_report';
    const GENERAL_ACCOUNTING_REPORT = 'general_accounting_report';
    const CUSTOMER_WISE_SALES_REPORT = 'customer_wise_sales_report';
    const PRODUCT_WISE_SALES_REPORT = 'product_wise_sales_report';
}