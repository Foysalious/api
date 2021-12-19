<?php namespace Sheba\Reward\Event;

use Sheba\Helpers\ConstGetter;

class Types
{
    use ConstGetter;

    const RATING = 'rating';
    const PARTNER_WALLET_RECHARGE = 'partner_wallet_recharge';
    const ORDER_SERVE = 'order_serve';
    const PARTNER_CREATION_BONUS = 'partner_creation_bonus';
    const POS_INVENTORY_CREATE = 'pos_inventory_create';
    const POS_CUSTOMER_CREATE = 'pos_customer_create';
    const DAILY_USAGE = 'daily_usage';
    const PAYMENT_LINK_USAGE = 'payment_link_usage';
    const POS_ORDER_CREATE = 'pos_order_create';
    const TOP_UP = 'top_up';
    const POS_ENTRY = 'pos_entry';
    const DUE_ENTRY = 'due_entry';
    const WALLET_CASHBACK = 'wallet_cashback';
    const ORDER_SERVER_AND_PAID = 'order_serve_and_paid';
    const PROFILE_COMPLETE = 'profile_complete';
    const INFO_CALL_COMPLETED = 'info_call_completed';

}