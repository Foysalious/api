<?php namespace App\Transformers;


use League\Fractal\TransformerAbstract;
use Sheba\Jobs\PreferredTime;

class PreferredTimeTransformer extends TransformerAbstract
{

    public function transform(PreferredTime $time)
    {
        return [
            "start" => $time->getStartString(),
            "end" => $time->getEndString(),
            "value" => $time->toReadableString(),
            "key" => $time->toString()
        ];
    }
}
