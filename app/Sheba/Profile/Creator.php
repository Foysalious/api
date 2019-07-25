<?php namespace Sheba\Profile;


use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;

class Creator
{
    private $profileRepository;
    private $mobile;
    private $mobileVerified;
    private $name;
    private $crateData;

    public function __construct(ProfileRepositoryInterface $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    public function setMobile($mobile)
    {
        $this->mobile = formatMobile($mobile);
        $this->mobileVerified = 1;
        return $this;
    }

    public function setName($name)
    {
        $this->name = ucfirst(trim($name));
        return $this;
    }

    public function create()
    {
        if ($profile = $this->profileRepository->findByMobile($this->mobile)->first()) return $profile;
        $this->makeData();
        return $this->profileRepository->create($this->crateData);
    }

    private function makeData()
    {
        $this->crateData['name'] = $this->name;
        $this->crateData['mobile'] = $this->mobile;
        $this->crateData['mobile_verified'] = $this->mobileVerified;
    }

}