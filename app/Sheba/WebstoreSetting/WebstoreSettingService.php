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

    public function getallSettings($partner)
    {
        $url = 'api/v1/partners/' . $partner . '/theme-settings';
        return $this->client->get($url);
    }

    public function getThemeDetails($partner)
    {
        $url = 'api/v1/partners/' . $partner . '/theme-setting-details';
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
}
