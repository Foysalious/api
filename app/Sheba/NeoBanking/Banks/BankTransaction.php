<?php


namespace Sheba\NeoBanking\Banks;


use Illuminate\Contracts\Support\Arrayable;
use Sheba\NeoBanking\Traits\JsonBreakerTrait;

class BankTransaction implements Arrayable
{
    use JsonBreakerTrait;

    protected $date;
    protected $name;
    protected $mobile;
    protected $amount;
    protected $type;
}
