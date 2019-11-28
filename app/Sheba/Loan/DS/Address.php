<?php


namespace Sheba\Loan\DS;


use Illuminate\Contracts\Support\Arrayable;

class Address implements Arrayable
{
    protected $country;
    protected $street;
    protected $thana;
    protected $zilla;
    protected $post_code;

    public function __construct($data)
    {
        if (is_string($data)) $data = json_decode($data, true);
        $data = $data ?: [];
        foreach ($data as $key => $item) {
            $this->$key = $item;
        }
    }

    /**
     * @return false|string
     * @throws \ReflectionException
     */
    public function toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function toArray()
    {
        $reflection_class = new \ReflectionClass($this);
        $data             = [];
        foreach ($reflection_class->getProperties() as $item) {
            $data[$item->name] = $this->{$item->name};
        }
        return $data;
    }

}
