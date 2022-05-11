<?php

namespace App\Sheba\WebstoreSetting;

class WebstoreSettingService
{
    /**
     *
     * @return array|object|string|null
     */
    public $client;
    private $settings;
    private $theme;
    private $partner;
    private $facebook;
    private $whatsapp;
    private $instagram;
    private $youtube;
    private $email;
    private $title;
    private $isPublish;
    private $image;
    private $type;
    private $bannerId;
    private $description;
    private $bannerImageLink;
    private $bannerTitle;
    private $bannerDescription;
    private $isPublished;


    public function __construct(WebstoreSettingServerClient $client)
    {
        $this->client = $client;
    }

    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }

    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
        return $this;
    }

    public function setInstagram($instagram)
    {
        $this->instagram = $instagram;
        return $this;
    }

    public function setWhatsapp($whatsapp)
    {
        $this->whatsapp = $whatsapp;
        return $this;
    }

    public function setYoutube($youtube)
    {
        $this->youtube = $youtube;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setIsPublish($isPublish)
    {
        $this->isPublish = $isPublish;
        return $this;
    }

    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }


    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setBannerId($bannerId)
    {
        $this->bannerId = $bannerId;
        return $this;
    }

    public function setBannerTitle($title)
    {
        $this->bannerTitle = $title;
        return $this;
    }

    public function setBannerDescription($description)
    {
        $this->bannerDescription = $description;
        return $this;
    }

    public function setBannerImageLink($bannerImageLink)
    {
        $this->bannerImageLink = $bannerImageLink;
        return $this;
    }

    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;
        return $this;
    }

    public function getallSettings($partner)
    {
        $url = 'api/v1/partners/' . $partner . '/theme-settings';
        return $this->client->get($url);
    }

    public function getPartnerSocialSettings($partner)
    {
        $url = 'api/v1/partners/' . $partner . '/social-settings';
        return $this->client->get($url);
    }

    public function getSystemDefinedSettings($partner)
    {
        $url = 'api/v1/theme-settings/system-defined';
        return $this->client->get($url);
    }

    public function getThemeDetails($partner)
    {
        $url = 'api/v1/partners/' . $partner . '/theme-settings/details';
        return $this->client->get($url);
    }

    public function makeStoreData()
    {
        $data = [];
        $data['partner_id'] = $this->partner;
        $data['theme_id'] = $this->theme;
        $data['settings'] = $this->settings;
        return $data;
    }

    public function store()
    {
        $data = $this->makeStoreData();
        return $this->client->post('api/v1/partners/' . $this->partner . '/theme-settings', $data);
    }

    public function makeStoreDataForSocialSettings()
    {
        $data = [];
        $data['facebook'] = $this->facebook;
        $data['instagram'] = $this->instagram;
        $data['whatsapp'] = $this->whatsapp;
        $data['youtube'] = $this->youtube;
        $data['email'] = $this->email;
        return $data;
    }

    public function makeUpdateDataForSocialSettings()
    {
        $data = [];
        if (isset($this->facebook)) $data['facebook'] = $this->facebook;
        if (isset($this->instagram)) $data['instagram'] = $this->instagram;
        if (isset($this->whatsapp)) $data['whatsapp'] = $this->whatsapp;
        if (isset($this->youtube)) $data['youtube'] = $this->youtube;
        if (isset($this->email)) $data['email'] = $this->email;
        return $data;
    }

    public function storeSocialSetting()
    {
        $data = $this->makeStoreDataForSocialSettings();
        return $this->client->post('api/v1/partners/' . $this->partner . '/social-settings', $data);
    }

    public function updateSocialSetting()
    {
        $data = $this->makeUpdateDataForSocialSettings();
        return $this->client->put('api/v1/partners/' . $this->partner . '/social-settings', $data);
    }


    public function update()
    {
        $data = $this->makeStoreData();
        return $this->client->put('api/v1/partners/' . $this->partner . '/theme-settings', $data);
    }

    public function sync()
    {
        $data = $this->makeStoreData();
        return $this->client->post('api/v1/partners/' . $this->partner . '/theme-settings/sync', $data);
    }

    public function getBannerSettings($partner)
    {
        $url = 'api/v1/partners/' . $partner . '/banners';
        return $this->client->get($url);
    }

    public function getBanner($partner, $banner)
    {
        $url = 'api/v1/partners/' . $partner . '/banners/' . $banner;
        return $this->client->get($url);
    }

    public function makeBannerStoreData()
    {
        $data = [];
        $data['partner_id'] = $this->partner;
        $data['title'] = $this->title;
        $data['description'] = $this->description;
        $data['is_published'] = $this->isPublish;
        $data['image_link'] = $this->image;
        return $data;
    }

    public function storeBanner()
    {
        $data = $this->makeBannerStoreData();
        return $this->client->post('api/v1/partners/' . $this->partner . '/banner-settings', $data);
    }

    public function makeBannerUpdateData()
    {
        $data = [];
        if (isset($this->title)) $data['title'] = $this->title;
        if (isset($this->description)) $data['description'] = $this->description;
        if (isset($this->isPublish)) $data['is_published'] = $this->isPublish;
        if (isset($this->image)) $data['image_link'] = $this->image;
        return $data;
    }

    public function updateBanner($banner)
    {
        $data = $this->makeBannerUpdateData();
        return $this->client->put('api/v1/partners/' . $this->partner . '/banners/' . $banner, $data);
    }

    public function getBannerList()
    {
        return $this->client->get('api/v1/partners/' . $this->partner . '/banners/'.$this->type);
    }

    public function getPageDetails()
    {
        return $this->client->get('api/v1/partners/' . $this->partner . '/page-settings/'.$this->type);
    }

    public function storePageSettings()
    {
        $data = $this->createPageSettingsData();
        return $this->client->put('api/v1/partners/' . $this->partner . '/page-settings/'.$this->type, $data);
    }

    private function createPageSettingsData()
    {
        return [
            "banner_id" => $this->bannerId ?? null,
            "banner_title" => $this->bannerTitle ?? null,
            "banner_description" => $this->bannerDescription ?? null,
            "banner_image_link" => $this->bannerImageLink ?? null,
            "description"  =>  $this->description ?? null,
            "is_published" => $this->isPublished ?? 1,
        ];
    }




}
