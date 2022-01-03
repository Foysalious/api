<?php namespace Sheba\PaymentLink;


class Target
{
    private $type;
    private $id;

    public function __construct($type, $id)
    {
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function toString()
    {
        return $this->type . ":" . $this->id;
    }
}
