<?php

namespace Sheba\Loan\DS;

use Illuminate\Contracts\Support\Arrayable;

class Address implements Arrayable
{
    use ReflectionArray;
    protected $country;
    protected $street;
    protected $thana;
    protected $zilla;
    protected $post_code;

}
