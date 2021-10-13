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

    public function getallSettings($partner)
    {
        $url = 'api/v1/partners/' . $partner . '/settings';
        return $this->client->get($url);
    }

    public function getPartnerSocialSettings($partner)
    {
        $url = 'api/v1/partners/' . $partner . '/setting-details';
        return $this->client->get($url);
    }

    public function getThemeDetails($partner)
    {
        $url = 'api/v1/partners/' . $partner . '/setting-details';
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
        return $this->client->post('api/v1/partners/' . $this->partner . '/settings', $data);
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
        $data['facebook'] = $this->facebook;
        $data['instagram'] = $this->instagram;
        $data['whatsapp'] = $this->whatsapp;
        $data['youtube'] = $this->youtube;
        $data['email'] = $this->email;
        return $data;
    }

    public function storeSocialSetting()
    {
        $data = $this->makeStoreDataForSocialSettings();
        return $this->client->post('api/v1/partners/' . $this->partner . '/social-settings', $data);
    }

    public function updateSocialSetting()
    {
        $data = $this->makeStoreDataForSocialSettings();
        return $this->client->post('api/v1/partners/' . $this->partner . '/social-settings', $data);
    }


    public function update()
    {
        $data = $this->makeStoreData();
        return $this->client->put('api/v1/partners/' . $this->partner . '/settings', $data);
    }
}
