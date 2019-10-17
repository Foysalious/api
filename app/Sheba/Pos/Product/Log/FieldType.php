<?php namespace Sheba\Pos\Product\Log;

use Sheba\Helpers\ConstGetter;

class FieldType
{
    use ConstGetter;

    const STOCK = 'stock';
    const UNIT = 'unit';
    const PRICE = 'price';

    public function getFieldsNameInBangla()
    {

    }
}