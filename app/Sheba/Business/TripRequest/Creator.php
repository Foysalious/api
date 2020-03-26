<?php namespace App\Sheba\Business\TripRequest;

use App\Models\BusinessMember;
use App\Models\BusinessTripRequest;
use Sheba\Location\Geo;
use Sheba\Repositories\Interfaces\Business\TripRequestRepositoryInterface;
use Sheba\Business\TripRequestApproval\Creator as TripRequestApprovalCreator;
use DB;

class Creator
{
    /** @var BusinessMember */
    private $businessMember;
    /** @var Geo */
    private $pickupGeo;
    /** @var Geo */
    private $dropoffGeo;
    /** @var TripRequestRepositoryInterface */
    private $tripRequestRepository;
    /** @var TripRequestApprovalCreator */
    private $tripRequestApprovalCreator;
    private $driverId;
    private $vehicleId;
    private $pickupAddress;
    private $dropoffAddress;
    private $startDate;
    private $endDate;
    private $tripType;
    private $vehicleType;
    private $reason;
    private $details;
    private $noOfSeats;

    /**
     * Creator constructor.
     * @param TripRequestRepositoryInterface $trip_request_repository
     * @param TripRequestApprovalCreator $creator
     */
    public function __construct(TripRequestRepositoryInterface $trip_request_repository, TripRequestApprovalCreator $creator)
    {
        $this->tripRequestRepository = $trip_request_repository;
        $this->tripRequestApprovalCreator = $creator;
    }

    /**
     * @param BusinessMember $businessMember
     * @return Creator
     */
    public function setBusinessMember($businessMember)
    {
        $this->businessMember = $businessMember;
        return $this;
    }

    /**
     * @param mixed $driverId
     * @return Creator
     */
    public function setDriverId($driverId)
    {
        $this->driverId = $driverId;
        return $this;
    }

    /**
     * @param mixed $vehicleId
     * @return Creator
     */
    public function setVehicleId($vehicleId)
    {
        $this->vehicleId = $vehicleId;
        return $this;
    }

    /**
     * @param Geo $pickupGeo
     * @return Creator
     */
    public function setPickupGeo($pickupGeo)
    {
        $this->pickupGeo = $pickupGeo;
        return $this;
    }

    /**
     * @param Geo $dropoffGeo
     * @return Creator
     */
    public function setDropoffGeo($dropoffGeo)
    {
        $this->dropoffGeo = $dropoffGeo;
        return $this;
    }

    /**
     * @param mixed $pickupAddress
     * @return Creator
     */
    public function setPickupAddress($pickupAddress)
    {
        $this->pickupAddress = $pickupAddress;
        return $this;
    }

    /**
     * @param mixed $dropoffAddress
     * @return Creator
     */
    public function setDropoffAddress($dropoffAddress)
    {
        $this->dropoffAddress = $dropoffAddress;
        return $this;
    }

    /**
     * @param mixed $startDate
     * @return Creator
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @param mixed $endDate
     * @return Creator
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @param mixed $tripType
     * @return Creator
     */
    public function setTripType($tripType)
    {
        $this->tripType = $tripType;
        return $this;
    }

    /**
     * @param mixed $vehicleType
     * @return Creator
     */
    public function setVehicleType($vehicleType)
    {
        $this->vehicleType = $vehicleType;
        return $this;
    }

    /**
     * @param mixed $reason
     * @return Creator
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * @param mixed $details
     * @return Creator
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param mixed $noOfSeats
     * @return Creator
     */
    public function setNoOfSeats($noOfSeats)
    {
        $this->noOfSeats = $noOfSeats;
        return $this;
    }

    public function create()
    {
        $trip_request = null;
        /** @var BusinessTripRequest $trip_request */
        $trip_request = $this->tripRequestRepository->create([
            'member_id' => $this->businessMember->member_id,
            'business_id' => $this->businessMember->business_id,
            'driver_id' => $this->driverId,
            'vehicle_id' => $this->vehicleId,
            'pickup_geo' => $this->pickupGeo ? json_encode(['lat' => $this->pickupGeo->getLat(), 'lng' => $this->pickupGeo->getLng()]) : null,
            'dropoff_geo' => $this->dropoffGeo ? json_encode(['lat' => $this->dropoffGeo->getLat(), 'lng' => $this->dropoffGeo->getLng()]) : null,
            'pickup_address' => $this->pickupAddress,
            'dropoff_address' => $this->dropoffAddress,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'trip_type' => $this->tripType,
            'vehicle_type' => $this->vehicleType,
            'reason' => $this->reason,
            'details' => $this->details,
            'no_of_seats' => $this->noOfSeats,
        ]);
        $this->tripRequestApprovalCreator->setTripRequest($trip_request)->create();

        return $trip_request;
    }
}
