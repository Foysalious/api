<?php namespace Sheba\Pos\Order;

use Sheba\Helpers\ConstGetter;

class PosOrderTypes
{
    use ConstGetter;

    const NEW_POS_ORDER = 'new_pos_order';
    const OLD_POS_ORDER = 'old_pos_order';
}