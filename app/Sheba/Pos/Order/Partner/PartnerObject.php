<?php namespace App\Sheba\Pos\Order\Partner;


class PartnerObject
{
    private $id;
    private $sub_domain;

    /**
     * @param mixed $id
     * @return PartnerObject
     */
    public function setId($id): PartnerObject
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $sub_domain
     * @return PartnerObject
     */
    public function setSubDomain($sub_domain): PartnerObject
    {
        $this->sub_domain = $sub_domain;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }


}