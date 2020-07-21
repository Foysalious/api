<?php namespace Sheba\Logistics\LogisticsNatures;

use App\Models\Job;
use Sheba\Logistics\Literals\Natures;
use Sheba\Logistics\Literals\OrderKeys;

class NatureFactory
{
    /**
     * @param Job $job
     * @param $order_key
     * @return LogisticNature
     */
    public static function getLogisticNature(Job $job, $order_key)
    {
        $nature = $job->logistic_nature;

        return ((function () use ($nature, $order_key) {
            if ($nature == Natures::ONE_WAY) {
                return app(OneWayLogistic::class);
            } else if ($nature == Natures::TWO_WAY) {
                if ($order_key == OrderKeys::FIRST) {
                    return app(TwoWayLogisticFirstOrder::class);
                } else if ($order_key == OrderKeys::LAST) {
                    return app(TwoWayLogisticLastOrder::class);
                }
            } else {
                throw new \Exception('Unsupported Nature');
            }
        })())->setJob($job);
    }
}
