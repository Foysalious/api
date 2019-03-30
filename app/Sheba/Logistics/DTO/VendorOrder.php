<?php namespace Sheba\Logistics\DTO;

use Sheba\Helpers\BasicGetter;

class VendorOrder 
{
    use BasicGetter;
    
    /** @var string */
    private $billUrl;
    /** @var string */
    private $detailUrl;
    
    /**
     * @param string $url
     * @return VendorOrder
     */
    public function setBillUrl($url)
    {
        $this->billUrl = $url;
        return $this;
    }
    
    /**
     * @param string $url
     * @return VendorOrder
     */
    public function setDetailUrl($url)
    {
        $this->detailUrl = $url;
        return $this;
    }
    
    
    /**
     * @return array
     */
    public function toArray() 
    {
        return [
            'bill_url' => $this->billUrl,
            'detail_url' => $this->detailUrl,
        ];
    }
    
    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
