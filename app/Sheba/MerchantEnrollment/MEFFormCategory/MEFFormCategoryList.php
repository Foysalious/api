<?php

namespace Sheba\MerchantEnrollment\MEFFormCategory;

use ArrayObject;
use Sheba\MerchantEnrollment\Exceptions\InvalidListInsertionException;

class MEFFormCategoryList extends ArrayObject
{
    /**
     * @throws InvalidListInsertionException
     */
    public function append($value)
    {
        if (!($value instanceof MEFFormCategory)) throw new InvalidListInsertionException();
        parent::append($value);
    }
}