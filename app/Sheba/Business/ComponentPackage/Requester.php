<?php namespace App\Sheba\Business\ComponentPackage;


class Requester
{
    private $packagesForAdd;
    private $packagesForUpdate;

    public function setPackage($packages)
    {
        $packages = json_decode($packages, 1);
        $this->packagesForAdd = $packages['add'];
        $this->packagesForUpdate = $packages['update'];

        return $this;
    }

    public function getPackagesForAdd()
    {
        return $this->packagesForAdd;
    }

    public function getPackagesForUpdate()
    {
        return $this->packagesForUpdate;
    }
}