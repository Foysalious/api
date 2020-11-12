<?php


namespace Sheba\Location;


use App\Models\Thana;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;

class FromGeo
{
    private $thanas;

    public function setThanas()
    {
        $this->thanas = Thana::all();
        return $this;
    }

    public function getThana($lat, $lng)
    {
        $current = new Coords($lat, $lng);
        $to = $this->thanas->map(function ($model) {
            return new Coords(floatval($model->lat), floatval($model->lng), $model->id);
        })->toArray();
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
        $results = $distance->from([$current])->to($to)->sortedDistance()[0];
        $result = array_keys($results)[0];
        return $this->thanas->where('id', $result)->first();
    }
}