<?php
/**
 * Created by PhpStorm.
 * User: Irteza
 * Date: 3/13/2019
 * Time: 7:27 PM
 */

namespace Sheba\Logistics\LogisticsNatures;


class NatureFactory
{
    /**
     * @param $nature
     * @return LogisticNature
     * @throws \Exception
     */
    public static function getLogisticNature($nature)
    {
        switch ($nature) {
            case 'one_way':
                return app(OneWayLogistic::class);
            case 'two_way':
                return app(TwoWayLogistic::class);
            default:
                throw new \Exception('Unsupported Nature');
        }
    }
}