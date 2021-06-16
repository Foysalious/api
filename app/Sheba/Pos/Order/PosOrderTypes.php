<?php namespace Sheba\Pos\Order;

use Sheba\Helpers\ConstGetter;

class PosOrderTypes
{
    use ConstGetter;

    const NEW_SYSTEM = 'new_system';
    const OLD_SYSTEM = 'old_system';
}