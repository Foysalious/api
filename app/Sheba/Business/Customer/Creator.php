<?php namespace Sheba\Business\Customer;


use App\Models\Member;
use App\Sheba\Address\AddressValidator;
use Sheba\Location\Coords;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\CustomerRepositoryInterface;
use Sheba\Customer\Creator as CustomerCreator;

class Creator
{
    use ModificationFields;
    private $customerRepository;
    private $customerCreator;
    /** @var Member */
    private $member;

    public function __construct(CustomerRepositoryInterface $customer_repository, CustomerCreator $customer_creator)
    {
        $this->customerRepository = $customer_repository;
        $this->customerCreator = $customer_creator;
    }

    public function setMember(Member $member)
    {
        $this->member = $member;
        return $this;
    }

    public function create()
    {
        $customer = $this->member->profile->customer;
        if (!$customer) $customer = $this->customerCreator->setMobile($this->member->profile->mobile)->create();
    }
}