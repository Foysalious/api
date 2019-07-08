<?php namespace Sheba\Business\Vehicle;

use App\Models\Vehicle;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\VehicleRepositoryInterface;
use DB;

class Creator
{
    use ModificationFields;

    /** @var CreateRequest $vehicleCreateRequest */
    private $vehicleCreateRequest;
    /** @var VehicleRepositoryInterface $vehicleRepository */
    private $vehicleRepository;
    /** @var CreateValidator $validator */
    private $validator;

    public function __construct(VehicleRepositoryInterface $vehicle_repo, CreateValidator $validator)
    {
        $this->vehicleRepository = $vehicle_repo;
        $this->validator = $validator;
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
            $vehicle = $this->vehicleRepository->create($this->formatVehicleSpecificData());
            $vehicle->basicInformations()->create($this->withCreateModificationField($this->formatVehicleBasicInfoSpecificData()));
            $vehicle->registrationInformations()->create($this->withCreateModificationField($this->formatVehicleRegistrationInfoSpecificData()));
        });
    }

    private function formatVehicleSpecificData()
    {
        return [
            'owner_type' => get_class($this->vehicleCreateRequest->getBusiness()),
            'owner_id' => $this->vehicleCreateRequest->getBusiness()->id,
            'business_department_id' => 1,
            'status' => 'active'
        ];
    }

    private function formatVehicleBasicInfoSpecificData()
    {
        return [
            'model_name' => $this->vehicleCreateRequest->getModelName(),
            'model_year' => $this->vehicleCreateRequest->getModelYear(),
            'seat_capacity' => $this->vehicleCreateRequest->getSeatCapacity(),
            'transmission_type' => $this->vehicleCreateRequest->getTransmissionType()
        ];
    }

    private function formatVehicleRegistrationInfoSpecificData()
    {
        return [
            'license_number' => $this->vehicleCreateRequest->getLicenseNumber(),
            'tax_token_number' => $this->vehicleCreateRequest->getTaxTokenNumber(),
            'fitness_start_date' => $this->vehicleCreateRequest->getFitnessValidityStart(),
            'fitness_end_date' => $this->vehicleCreateRequest->getFitnessValidityEnd()
        ];
    }
}