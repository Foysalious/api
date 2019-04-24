<?php namespace Sheba\Pos\Order\RefundNatures;

use App\Models\PosOrder;

class NatureFactory
{
    public static function getRefundNature(PosOrder $order, $nature)
    {
        return ((function () use ($order, $nature) {
            if ($nature == Natures::RETURNED) {
                return app(ReturnPosItem::class);
            } else if ($nature == Natures::EXCHANGED) {
                return app(ExchangePosItem::class);
            } else {
                throw new \Exception('Unsupported Nature');
            }
        })())->setOrder($order);
    }
}