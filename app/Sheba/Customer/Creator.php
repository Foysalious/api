<?php namespace Sheba\Customer;

use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\CustomerRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use Sheba\Profile\Creator as ProfileCreator;

class Creator
{
    use ModificationFields;

    private $profileRepository;
    private $customerRepository;
    private $profileCreator;
    private $mobile;
    private $name;

    public function __construct(ProfileRepositoryInterface $profileRepository, CustomerRepositoryInterface $customerRepository, ProfileCreator $profileCreator)
    {
        $this->profileRepository = $profileRepository;
        $this->customerRepository = $customerRepository;
        $this->profileCreator = $profileCreator;
        $this->name = '';
    }

    public function setMobile($mobile)
    {
        $this->mobile = formatMobile($mobile);
        return $this;
    }

    public function setName($name)
    {
        $this->name = ucfirst(trim($name));
        return $this;
    }

    public function create()
    {
        $profile = $this->profileRepository->findByMobile($this->mobile)->first();
        if (!$profile) $profile = $this->profileCreator->setName($this->name)->setMobile($this->mobile)->create();
        $customer = $profile->customer;
        $this->setModifier($profile);
        if (!$customer) $customer = $this->customerRepository->create(['profile_id' => $profile->id]);
        return $customer;
    }


}