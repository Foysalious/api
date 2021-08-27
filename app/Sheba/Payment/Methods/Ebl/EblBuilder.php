<?php


namespace Sheba\Payment\Methods\Ebl;
use App\Models\Payable;
use Sheba\Payment\Methods\Ebl\Stores\DefaultEblStore;
use Sheba\Payment\Methods\Nagad\Nagad;

class EblBuilder
{
    /**
     * @param Payable $payable
     * @return Ebl
     */
    public static function get(Payable $payable)
    {
        /** @var Ebl $ebl */
        $ebl=app(Ebl::class);
        $ebl->setStore(self::getStore($payable));
        return $ebl;

    }
    public static function getStore(Payable $payable){
        if ($payable->readable_type=='payment_link')return new DefaultEblStore();
        return new DefaultEblStore();
    }
}
