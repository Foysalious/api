<?php namespace Sheba\Business\Driver;

use App\Models\Business;
use App\Models\Driver;
use App\Models\Member;
use App\Models\Profile;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use DB;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\Interfaces\DriverRepositoryInterface;
use Sheba\Repositories\Interfaces\HiredDriverRepositoryInterface;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Sheba\Repositories\ProfileRepository;

class Creator
{
    /** @var CreateRequest $driverCreateRequest */
    private $driverCreateRequest;
    /** @var FileRepository $fileRepository */
    private $fileRepository;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var DriverRepositoryInterface $driverRepository */
    private $driverRepository;
    /** @var MemberRepositoryInterface $memberRepository */
    private $memberRepository;
    /** @var Profile $profile */
    private $profile;
    /** @var Member $member */
    private $member;
    /** @var Business $business */
    private $business;
    /** @var BusinessMemberRepositoryInterface $businessMemberRepository */
    private $businessMemberRepository;
    /** @var CreateValidator $validator */
    private $validator;
    /** @var HiredDriverRepositoryInterface $hiredDriverRepository */
    private $hiredDriverRepository;
    /** @var Driver $driver */
    private $driver;

    public function __construct(FileRepository $file_repository, ProfileRepository $profile_repository,
                                DriverRepositoryInterface $driver_repo, MemberRepositoryInterface $member_repo,
                                BusinessMemberRepositoryInterface $business_member_repo, CreateValidator $validator,
                                HiredDriverRepositoryInterface $hired_driver_repo)
    {
        $this->fileRepository = $file_repository;
        $this->profileRepository = $profile_repository;
        $this->driverRepository = $driver_repo;
        $this->memberRepository = $member_repo;
        $this->businessMemberRepository = $business_member_repo;
        $this->validator = $validator;
        $this->hiredDriverRepository = $hired_driver_repo;
    }

    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setDriverCreateRequest(CreateRequest $create_request)
    {
        $this->driverCreateRequest = $create_request;
        return $this;
    }

    public function hasError()
    {
        $this->validator->setDriverCreateRequest($this->driverCreateRequest);
        return $this->validator->hasError();
    }

    public function create()
    {
        DB::transaction(function () {
            $this->profile = $this->profileRepository->checkExistingMobile($this->driverCreateRequest->getMobile());
            if (!$this->profile) $this->profile = $this->profileRepository->store($this->formatProfileSpecificData());
            $this->driver = $this->profile->driver;
            if (!$this->driver) {
                $this->driver = $this->driverRepository->create($this->formatDriverSpecificData());
                $this->profileRepository->update($this->profile, ['driver_id' => $this->driver->id]);
            }
            $this->member = $this->profile->member;
            if (!$this->member) $this->member = $this->memberRepository->create($this->formatMemberSpecificData());
            $this->business = $this->driverCreateRequest->getAdminMember()->businesses->first();
            $this->businessMemberRepository->create($this->formatBusinessSpecificData());
            if ($this->driverCreateRequest->getVendorMobile()) $this->attachWithVendor();
        });
    }

    private function formatDriverSpecificData()
    {
        return [
            'status' => 'active',
            'license_number' => $this->driverCreateRequest->getLicenseNumber(),
            'license_number_end_date' => $this->driverCreateRequest->getLicenseNumberEndDate(),
            'license_class' => $this->driverCreateRequest->getLicenseClass()
        ];
    }

    private function formatProfileSpecificData()
    {
        return [
            'remember_token' => str_random(255),
            'mobile' => $this->driverCreateRequest->getMobile(),
            'name' => $this->driverCreateRequest->getName(),
            'address' => $this->driverCreateRequest->getAddress(),
            'dob' => $this->driverCreateRequest->getDateOfBirth(),
            'nid_no' => $this->driverCreateRequest->getNidNumber()
        ];
    }

    private function formatMemberSpecificData()
    {
        return [
            'profile_id' => $this->profile->id,
            'remember_token' => str_random(255)
        ];
    }

    private function formatBusinessSpecificData()
    {
        return [
            'business_id' => $this->business->id,
            'member_id' => $this->member->id,
            'type' => 'Admin',
            'join_date' => Carbon::now()
        ];
    }

    private function attachWithVendor()
    {
        $resource_mobile = $this->driverCreateRequest->getVendorMobile();
        $profile = $this->profileRepository->checkExistingMobile($resource_mobile);
        $partner = $profile->resource->firstPartner();
        $this->hiredDriverRepository->create([
            'hired_by_type' => get_class($this->business),
            'hired_by_id' => $this->business->id,
            'owner_type' => "App\Models\Partner",
            'owner_id' => $partner->id,
            'driver_id' => $this->driver->id,
            'start' => Carbon::now()
        ]);
    }
}