<?php namespace Sheba\Business\Vehicle;

use App\Models\BusinessDepartment;
use App\Models\Profile;
use App\Models\Vehicle;
use Carbon\Carbon;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\HiredVehicleRepositoryInterface;
use Sheba\Repositories\Interfaces\VehicleRepositoryInterface;
use DB;
use Sheba\Repositories\ProfileRepository;

class Creator
{
    use ModificationFields;

    /** @var CreateRequest $vehicleCreateRequest */
    private $vehicleCreateRequest;
    /** @var VehicleRepositoryInterface $vehicleRepository */
    private $vehicleRepository;
    /** @var CreateValidator $validator */
    private $validator;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var HiredVehicleRepositoryInterface $hiredVehicleRepository */
    private $hiredVehicleRepository;
    /** @var Vehicle $vehicle */
    private $vehicle;

    public function __construct(VehicleRepositoryInterface $vehicle_repo, CreateValidator $validator,
                                ProfileRepository $profile_repository, HiredVehicleRepositoryInterface $hired_vehicle_repo)
    {
        $this->vehicleRepository = $vehicle_repo;
        $this->validator = $validator;
        $this->profileRepository = $profile_repository;
        $this->hiredVehicleRepository = $hired_vehicle_repo;
    }

    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setVehicleCreateRequest(CreateRequest $create_request)
    {
        $this->vehicleCreateRequest = $create_request;
        return $this;
    }

    public function hasError()
    {
        $this->validator->setVehicleCreateRequest($this->vehicleCreateRequest);
        return $this->validator->hasError();
    }

    public function create()
    {
        DB::transaction(function () {
            /** @var Vehicle $vehicle */
            $this->vehicle = $this->vehicleRepository->create($this->formatVehicleSpecificData());
            $this->vehicle->basicInformations()->create($this->withCreateModificationField($this->formatVehicleBasicInfoSpecificData()));
            $this->vehicle->registrationInformations()->create($this->withCreateModificationField($this->formatVehicleRegistrationInfoSpecificData()));
            if ($this->vehicleCreateRequest->getVendorPhoneNumber())
                $this->attachWithVendor();
        });
    }

    private function formatVehicleSpecificData()
    {
        $business_department_id = null;
        $business = $this->vehicleCreateRequest->getBusiness();

        if ($this->vehicleCreateRequest->getVendorPhoneNumber()) {
            $resource_mobile = $this->vehicleCreateRequest->getVendorPhoneNumber();
            /** @var Profile $profile */
            $profile = $this->profileRepository->checkExistingMobile($resource_mobile);
            $partner = $profile->resource->firstPartner();

            $owner_type = get_class($partner);
            $owner_id = $partner->id;
        } else {
            $owner_type = get_class($business);
            $owner_id = $business->id;
        }

        $department_name = $this->vehicleCreateRequest->getVehicleDepartment();
        $business_department = BusinessDepartment::where([
            ['business_id', $business->id],
            ['name', 'like', '%'.$department_name.'%']
        ])->first();
        $business_department_id = $business_department ? $business_department->id : null;

        return [
            'owner_type' => $owner_type,
            'owner_id' => $owner_id,
            'business_department_id' => $business_department_id,
            'status' => 'active'
        ];
    }

    private function formatVehicleBasicInfoSpecificData()
    {
        return [
            'model_name' => $this->vehicleCreateRequest->getModelName(),
            'model_year' => $this->vehicleCreateRequest->getModelYear(),
            'seat_capacity' => $this->vehicleCreateRequest->getSeatCapacity(),
            'transmission_type' => $this->vehicleCreateRequest->getTransmissionType(),
            'company_name' => $this->vehicleCreateRequest->getVehicleBrandName(),
            'type' => $this->vehicleCreateRequest->getVehicleType()
        ];
    }

    private function formatVehicleRegistrationInfoSpecificData()
    {
        return [
            'license_number' => $this->vehicleCreateRequest->getLicenseNumber(),
            'license_number_end_date' => $this->vehicleCreateRequest->getLicenseNumberEndDate(),
            'tax_token_number' => $this->vehicleCreateRequest->getTaxTokenNumber(),
            'fitness_start_date' => $this->vehicleCreateRequest->getFitnessValidityStart(),
            'fitness_end_date' => $this->vehicleCreateRequest->getFitnessValidityEnd()
        ];
    }

    private function attachWithVendor()
    {
        $resource_mobile = $this->vehicleCreateRequest->getVendorPhoneNumber();
        $profile = $this->profileRepository->checkExistingMobile($resource_mobile);
        $partner = $profile->resource->firstPartner();

        $this->hiredVehicleRepository->create([
            'hired_by_type' => get_class($this->vehicleCreateRequest->getBusiness()),
            'hired_by_id' => $this->vehicleCreateRequest->getBusiness()->id,
            'owner_type' => get_class($partner),
            'owner_id' => $partner->id,
            'vehicle_id' => $this->vehicle->id,
            'start' => Carbon::now()
        ]);
    }
}