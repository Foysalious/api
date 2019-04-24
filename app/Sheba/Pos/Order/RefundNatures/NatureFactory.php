<?php namespace Sheba\Pos\Order\RefundNatures;

use App\Models\PosOrder;

class NatureFactory
{
    public static function getRefundNature(PosOrder $order, array $data, $nature, $return_nature)
    {
        return ((function () use ($order, $nature, $return_nature) {
            if ($nature == Natures::RETURNED) {
                if ($return_nature == ReturnNatures::PARTIAL_RETURN) {
                    return app(PartialReturnPosItem::class);
                } elseif ($return_nature == ReturnNatures::FULL_RETURN) {
                    return app(FullReturnPosItem::class);
                } elseif ($return_nature == ReturnNatures::QUANTITY_INCREASE) {
                    return app(PosItemQuantityIncrease::class);
                }
            } else if ($nature == Natures::EXCHANGED) {
                return app(ExchangePosItem::class);
            } else {
                throw new \Exception('Unsupported Nature');
            }
        })())->setOrder($order)->setData($data);
    }
}