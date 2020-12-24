<?php namespace App\Http\Presenters;

use Illuminate\Contracts\Support\Arrayable;

abstract class Presenter implements Arrayable
{
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
