<?php


namespace App\Sheba\NeoBanking\Banks;

use Illuminate\Contracts\Support\Arrayable;
use Sheba\NeoBanking\Traits\JsonBreakerTrait;

class NidDetailsInfo implements Arrayable
{
    use JsonBreakerTrait;

    protected $nidInfo;

    /**
     * @param $data
     * @return $this
     */
    public function getData($data)
    {
        $this->nidInfo = $data;
        return $this;
    }
}