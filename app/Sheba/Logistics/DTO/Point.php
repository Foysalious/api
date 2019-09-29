<?php namespace Sheba\Logistics\DTO;

use Sheba\Location\Coords;
use Sheba\Helpers\BasicGetter;

class Point 
{
    use BasicGetter;
    
    private $name;
    private $image;
    private $mobile;
    private $address;
    /** @var Coords */
    private $coordinate;

    /**
     * @param string $name
     *
     * @return Point
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $image
     *
     * @return Point
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param string $mobile
     *
     * @return Point
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @param string $address
     *
     * @return Point
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @param Coords $coordinate
     *
     * @return Point
     */
    public function setCoordinate(Coords $coordinate)
    {
        $this->coordinate = $coordinate;
        return $this;
    }
}
