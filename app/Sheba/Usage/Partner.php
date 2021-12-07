<?php

namespace Sheba\Usage;
class Partner
{
    const POS_ORDER_CREATE            = 'pos_order_create';
    const INVENTORY_CREATE            = 'inventory_create';
    const EXPENSE_TRACKER_TRANSACTION = 'expense_tracker_transaction';
    const SMS_MARKETING               = 'sms_marketing';
    const POS_DUE_COLLECTION          = 'pos_due_collection';
    const PRODUCT_LINK                = 'product_link';
    const PAYMENT_LINK                = 'payment_link';
    const DUE_TRACKER_TRANSACTION     = 'due_tracker_transaction';

    const TRANSACTION_COMPLETE        = 'transaction_complete';
    const TOPUP_COMPLETE              = 'topup_complete';


    const CREATE_CUSTOMER             = 'customer_create';

    const PAYMENT_COLLECT             = 'payment_collect';
}
