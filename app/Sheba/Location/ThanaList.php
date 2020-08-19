<?php namespace Sheba\Location;

use App\Models\Thana;

class ThanaList
{
    /**
     * @var Thana
     */
    private $thana;
    private $limit;
    private $offset;

    public function __construct(Thana $thana)
    {
        $this->thana = $thana;
    }

    /**
     * @param $limit
     * @return ThanaList
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param $offset
     * @return ThanaList
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function getAllThana()
    {
        return $this->thana->offset($this->offset)->limit($this->limit)->select('id', 'name', 'bn_name', 'lat', 'lng')->get();
    }
}