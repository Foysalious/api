<?php namespace Sheba;

use Illuminate\Contracts\Support\Arrayable;

abstract class PresentableDTO implements Arrayable
{
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
