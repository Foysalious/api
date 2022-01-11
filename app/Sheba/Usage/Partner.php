<?php

namespace Sheba\Usage;
class Partner
{
    const POS_ORDER_CREATE            = 'pos_order_create';
    const POS_ORDER_UPDATE            = 'pos_order_update';
    const POS_ORDER_DELETE            = 'pos_order_delete';
    const INVENTORY_CREATE            = 'inventory_create';
    const INVENTORY_UPDATE            = 'inventory_update';
    const INVENTORY_DELETE            = 'inventory_delete';
    const EXPENSE_TRACKER_TRANSACTION = 'expense_tracker_transaction';
    const SMS_MARKETING               = 'sms_marketing';
    const POS_DUE_COLLECTION          = 'pos_due_collection';
    const PRODUCT_LINK                = 'product_link';
    const PAYMENT_LINK                = 'payment_link';
    const DUE_TRACKER_TRANSACTION     = 'due_tracker_transaction';
    const TRANSACTION_COMPLETE        = 'transaction_complete';
    const TOPUP_COMPLETE              = 'topup_complete';
    const CREATE_CUSTOMER             = 'customer_create';
    const UPDATE_CUSTOMER             = 'customer_update';
    const DELETE_CUSTOMER             = 'customer_delete';

    const PAYMENT_COLLECT             = 'payment_collect';
    const DUE_ENTRY_UPDATE            = 'due_entry_update';
    const DUE_ENTRY_DELETE            = 'due_entry_delete';
    const EXPENSE_ENTRY_UPDATE        = 'expense_entry_update';
    const EXPENSE_ENTRY_DELETE        = 'expense_entry_delete';
    const TRANSFER_ENTRY_UPDATE       = 'transfer_entry_update';
    const REMINDER_SET                = 'due_reminder_set';
    const DUE_SMS_SEND                = 'due_sms_send';
    const TRANSFER_ENTRY              = 'account_transfer_entry';
    const ENTRY_DELETE                = 'entry_delete';
    const REPORT_DOWNLOAD             = 'accounting_report_download';
}
