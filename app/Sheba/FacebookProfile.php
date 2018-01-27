<?php

namespace App\Sheba;


class FacebookProfile
{
    public $name;
    public $email = null;
    public $picture;
    public $gender;
    public $id;
    public $fb_info;

    public function __construct($fb_info)
    {
        $this->fb_info = $fb_info;
        $this->set();
    }

    private function set()
    {
        $this->name = $this->fb_info['name'];
        if (isset($this->fb_info['email'])) {
            if ($this->fb_info['email'] != 'undefined') {
                $this->email = $this->fb_info['email'];
            }
        }
        $this->gender = isset($this->fb_info['gender']) ? ucfirst($this->fb_info['gender']) : null;
        $this->id = $this->fb_info['id'];
        $this->picture = $this->fb_info['picture']['data']['url'];
    }

    public function getProfileInformation()
    {
        return array(
            'fb_id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'gender' => $this->gender,
            'pro_pic' => $this->picture,
            'email_verified' => $this->email != null ? 1 : 0
        );
    }

    public function hasEmail()
    {
        return $this->email != null ? 1 : 0;
    }
}