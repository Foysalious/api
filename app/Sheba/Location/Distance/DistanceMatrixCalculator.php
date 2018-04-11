<?php namespace Sheba\Location\Distance;


use Sheba\Location\Distance\Strategies\GoogleDistanceMatrix;

class DistanceMatrixCalculator
{
    private $calculator;
    protected $from_array;
    protected $to_array;

    public function __construct(DistanceCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function from(array $from)
    {
        $this->from_array = $from;
        return $this;
    }

    public function to(array $to)
    {
        $this->to_array = $to;
        return $this;
    }

    public function distance()
    {
        if($this->calculator instanceof GoogleDistanceMatrix) {
            return $this->calculator->distanceFromArray($this->from_array, $this->to_array);
        }

        $result = [];
        foreach ($this->from_array as $i => $from) {
            foreach ($this->to_array as $j => $to) {
                $result[$i][$to->id ?: $j] = $this->calculator->from($from)->to($to)->distance();
            }
        }
        return $result;
    }

    public function sortedDistance()
    {
        $distances = $this->distance();
        foreach ($distances as $i => $distance) {
            asort($distances[$i]);
        }
        return $distances;
    }
}