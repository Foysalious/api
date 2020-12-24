<?php namespace App\Transformers\Business;

use App\Transformers\CustomSerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class TenderTransformer extends TransformerAbstract
{
    /**
     * @param $procurement
     * @return array
     */
    public function transform($procurement)
    {
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($procurement, new TenderDetailsTransformer());
        return $fractal->createData($resource)->toArray()['data'];
    }
}
