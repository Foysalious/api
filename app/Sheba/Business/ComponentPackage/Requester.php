<?php namespace App\Sheba\Business\ComponentPackage;


class Requester
{
    private $packagesForAdd;
    private $packagesForUpdate;
    private $packageDeleteData;

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

    public function setPackageDelete($package_delete_data)
    {
        $this->packageDeleteData = json_decode($package_delete_data,1);
        return $this;
    }

    public function getPackageDelete()
    {
        return $this->packageDeleteData;
    }
}