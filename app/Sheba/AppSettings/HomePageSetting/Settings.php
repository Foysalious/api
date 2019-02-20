<?php namespace Sheba\AppSettings\HomePageSetting;


class Settings
{
    protected $positions = [];

    public function push($data)
    {
        $this->positions[] = $data;
        return $this;
    }

    public function get()
    {
        return $this->positions;
    }

    public function toJson()
    {
        return json_encode($this->positions);
    }
}