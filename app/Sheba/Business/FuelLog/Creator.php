<?php namespace Sheba\Business\FuelLog;

use Carbon\Carbon;
use Sheba\Repositories\Interfaces\FuelLogRepositoryInterface;

class Creator
{
    private $fuelLogRepository;
    private $vehicleId;
    private $price;
    private $volume;
    private $unit;
    private $type;
    private $stationName;
    private $stationAddress;
    private $reference;
    private $data;
    /** @var Carbon */
    private $refilledDate;

    public function __construct(FuelLogRepositoryInterface $fuel_log_repository)
    {
        $this->fuelLogRepository = $fuel_log_repository;
    }

    public function setVehicleId($vehicle_id)
    {
        $this->vehicleId = $vehicle_id;
        return $this;
    }

    public function setDate($date)
    {
        $this->refilledDate = Carbon::parse($date);
        return $this;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    public function setVolume($volume)
    {
        $this->volume = $volume;
        return $this;
    }

    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setStationName($station_name)
    {
        $this->stationName = $station_name;
        return $this;
    }

    public function setStationAddress($station_address)
    {
        $this->stationAddress = $station_address;
        return $this;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    public function save()
    {
        $this->makeData();
        return $this->fuelLogRepository->create($this->data);
    }

    private function makeData()
    {
        $this->data = [
            'vehicle_id' => $this->vehicleId,
            'type' => $this->type,
            'unit' => $this->unit,
            'volume' => $this->volume,
            'price' => $this->price,
            'refilled_date' => $this->refilledDate->toDateTimeString(),
            'station_name' => $this->stationName,
            'station_address' => $this->stationAddress,
            'reference' => $this->reference,
        ];
    }

}