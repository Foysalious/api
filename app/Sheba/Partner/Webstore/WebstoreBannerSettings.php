<?php namespace App\Sheba\Partner\Webstore;


use App\Models\Partner;
use Sheba\Dal\PartnerWebstoreBanner\Model as PartnerWebstoreBanner ;
use Sheba\Dal\WebstoreBanner\Model as WebstoreBanner;
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

    /**
     * @return mixed
     */
    public function getBannerList()
    {
      return $banners = WebstoreBanner::get()->map(function($banner){
            return [
              'id' => $banner->id,
              'image_link' => $banner->image_link
            ];
        });
    }

}