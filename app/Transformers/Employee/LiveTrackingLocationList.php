<?php namespace App\Transformers\Employee;

use League\Fractal\TransformerAbstract;

class LiveTrackingLocationList extends TransformerAbstract
{
    public function transform()
    {

        return [
            'id' => 1
        ];
    }
}