<?php namespace App\Sheba\Partner\Webstore;


use App\Models\Partner;
use Sheba\Dal\PartnerWebstoreBanner\Model as PartnerWebstoreBanner ;
use Sheba\ModificationFields;

class WebstoreBannerSettings
{
    use ModificationFields;

    private $partner;
    private $data;

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return PartnerWebstoreBanner
     */
    public function store()
    {
        return PartnerWebstoreBanner::create($this->withCreateModificationField($this->data));
    }

}