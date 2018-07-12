<?php namespace Sheba\Resource;

use App\Models\Resource;

class ResourceVerification
{
    private $resource;
    private $missingInfo = [];
    private $isValid = true;

    public function check(Resource $resource)
    {
        $this->resource = $resource;
        $this->isNidNoEmpty();
        $this->isNidImageEmpty();
        $this->isNameEmpty();
        $this->isMobileEmpty();
        $this->isProfilePictureEmpty();
        $this->isCategoryEmpty();

        if ($this->isValid) return ['is_valid' => true];

        $msg = 'Please provide '. implode(', ', $this->missingInfo) . ' for verification';
        return ['is_valid' => false, "msg" => $msg];
    }

    private function isNidNoEmpty()
    {
        if (empty($this->resource->nid_no)){
            array_push($this->missingInfo,'nid_no');
            $this->isValid = false;
        }
    }

    private function isNidImageEmpty()
    {
        if (empty($this->resource->nid_image)) {
            array_push($this->missingInfo,'nid_image');
            $this->isValid = false;
        }
    }

    private function isNameEmpty()
    {
        if (empty($this->resource->profile->name)){
            array_push($this->missingInfo,'name');
            $this->isValid = false;
        }
    }

    private function isMobileEmpty()
    {
        if (empty($this->resource->profile->mobile)){
            array_push($this->missingInfo,'mobile');
            $this->isValid = false;
        }
    }

    private function isProfilePictureEmpty()
    {
        if (empty($this->resource->profile->pro_pic)){
            array_push($this->missingInfo,'pro_pic');
            $this->isValid = false;
        }
    }

    private function isCategoryEmpty()
    {
        $this->resource->partners->each(function ($partner) {
            if ($this->resource->isHandyman($partner) && $this->resource->categoriesIn($partner->id)->isEmpty()){
                array_push($this->missingInfo,'category');
                $this->isValid = false;
                return false;
            }
        });
    }

}