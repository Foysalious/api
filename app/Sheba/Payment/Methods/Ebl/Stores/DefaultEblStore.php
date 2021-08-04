<?php


namespace Sheba\Payment\Methods\Ebl\Stores;


class DefaultEblStore extends EblStore
{
    const NAME = 'default';


    function getName()
    {
        return self::NAME;
    }
}
