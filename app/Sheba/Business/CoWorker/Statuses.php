<?php namespace Sheba\Business\CoWorker;

use Sheba\Helpers\ConstGetter;

class Statuses
{
    use ConstGetter;

    const INVITED = 'invited';
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const DELETE = 'delete';

    public static function getAccessible()
    {
        return [self::ACTIVE, self::INVITED];
    }
}
