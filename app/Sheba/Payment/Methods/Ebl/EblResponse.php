<?php


namespace Sheba\Payment\Methods\Ebl;


use Illuminate\Contracts\Support\Arrayable;
use Sheba\Loan\DS\ReflectionArray;

class EblResponse implements Arrayable
{
    use ReflectionArray;
    private $data;

}
