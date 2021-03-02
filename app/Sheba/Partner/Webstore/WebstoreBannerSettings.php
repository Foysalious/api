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
    private $partnerBannerSettings;
    private $updatedData;

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
     * @param PartnerWebstoreBanner $partner_banner_settings
     * @return $this
     */
    public function setBannerSettings(PartnerWebstoreBanner $partner_banner_settings)
    {
        $this->partnerBannerSettings = $partner_banner_settings;
        return $this;
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

    /**
     * @return bool|int
     */
    public function update()
    {
        $this->format();
        if(!empty($this->updatedData))
       return $this->partnerBannerSettings->update($this->updatedData);
    }

    public function format()
    {
        if(isset($this->data['banner_id']) && $this->data['banner_id'] != $this->partnerBannerSettings->banner_id)
            $this->updatedData['banner_id'] = $this->data['banner_id'];
        if(isset($this->data['title']) && $this->data['title'] != $this->partnerBannerSettings->title)
            $this->updatedData['title'] = $this->data['title'];
        if(isset($this->data['description']) && $this->data['description'] != $this->partnerBannerSettings->description)
            $this->updatedData['description'] = $this->data['description'];
        if(isset($this->data['is_published']) && $this->data['is_published'] != $this->partnerBannerSettings->is_published)
            $this->updatedData['is_published'] = $this->data['is_published'];
    }

}