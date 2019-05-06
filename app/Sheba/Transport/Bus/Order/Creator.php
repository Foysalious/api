<?php namespace Sheba\Transport\Bus\Order;

use Sheba\Transport\Bus\Repositories\TransportTicketOrdersRepository;
use Sheba\Transport\TransportAgent;

class Creator
{
    private $agentType;
    private $agentId;
    private $reserverName;
    private $reserverMobile;
    private $reserverEmail;
    private $vendorId;
    private $status;
    private $amount;
    private $discount;
    private $discountPercent;
    private $shebaContribution;
    private $vendorContribution;
    private $transactionId;
    private $journeyDate;
    private $departureTime;
    private $arrivalTime;
    private $departureStationName;
    private $arrivalStationName;
    private $reservationDetails;
    /** @var TransportTicketOrdersRepository $ordersRepo */
    private $ordersRepo;

    public function __construct(TransportTicketOrdersRepository $orders_repo)
    {
        $this->ordersRepo = $orders_repo;
    }

    public function create()
    {
        $data = [
            'agent_type' => $this->agentType,
            'agent_id' => $this->agentId,
            'reserver_name' => $this->reserverName,
            'reserver_mobile' => $this->reserverMobile,
            'reserver_email' => $this->reserverEmail,
            'vendor_id' => $this->vendorId,
            'status' => $this->status,
            'amount' => $this->amount,
            'discount' => $this->discount ?: 0.00,
            'discount_percent' => $this->discountPercent ?: 0.00,
            'sheba_contribution' => $this->shebaContribution ?: 0.00,
            'vendor_contribution' => $this->vendorContribution ?: 0.00,
            'transaction_id' => $this->transactionId,
            'journey_date' => $this->journeyDate,
            'departure_time' => $this->departureTime,
            'arrival_time' => $this->arrivalTime,
            'departure_station_name' => $this->departureStationName,
            'arrival_station_name' => $this->arrivalStationName,
            'reservation_details' => $this->reservationDetails
        ];
        $order = $this->ordersRepo->save($data);

        return $order;
    }

    /**
     * @param TransportAgent $agent
     * @return $this
     */
    public function setAgent(TransportAgent $agent)
    {
        $this->agentType = get_class($agent);
        $this->agentId = $agent->id;
        return $this;
    }

    /**
     * @param mixed $reserverName
     * @return Creator
     */
    public function setReserverName($reserverName)
    {
        $this->reserverName = $reserverName;
        return $this;
    }

    /**
     * @param mixed $reserverMobile
     * @return Creator
     */
    public function setReserverMobile($reserverMobile)
    {
        $this->reserverMobile = $reserverMobile;
        return $this;
    }

    /**
     * @param mixed $reserverEmail
     * @return Creator
     */
    public function setReserverEmail($reserverEmail)
    {
        $this->reserverEmail = $reserverEmail;
        return $this;
    }

    /**
     * @param mixed $vendorId
     * @return Creator
     */
    public function setVendorId($vendorId)
    {
        $this->vendorId = $vendorId;
        return $this;
    }

    /**
     * @param mixed $status
     * @return Creator
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return Creator
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $discount
     * @return Creator
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @param mixed $discountPercent
     * @return Creator
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;
        return $this;
    }

    /**
     * @param mixed $shebaContribution
     * @return Creator
     */
    public function setShebaContribution($shebaContribution)
    {
        $this->shebaContribution = $shebaContribution;
        return $this;
    }

    /**
     * @param mixed $vendorContribution
     * @return Creator
     */
    public function setVendorContribution($vendorContribution)
    {
        $this->vendorContribution = $vendorContribution;
        return $this;
    }

    /**
     * @param mixed $transactionId
     * @return Creator
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @param mixed $journeyDate
     * @return Creator
     */
    public function setJourneyDate($journeyDate)
    {
        $this->journeyDate = $journeyDate;
        return $this;
    }

    /**
     * @param mixed $departureTime
     * @return Creator
     */
    public function setDepartureTime($departureTime)
    {
        $this->departureTime = $departureTime;
        return $this;
    }

    /**
     * @param mixed $arrivalTime
     * @return Creator
     */
    public function setArrivalTime($arrivalTime)
    {
        $this->arrivalTime = $arrivalTime;
        return $this;
    }

    /**
     * @param mixed $departureStationName
     * @return Creator
     */
    public function setDepartureStationName($departureStationName)
    {
        $this->departureStationName = $departureStationName;
        return $this;
    }

    /**
     * @param mixed $arrivalStationName
     * @return Creator
     */
    public function setArrivalStationName($arrivalStationName)
    {
        $this->arrivalStationName = $arrivalStationName;
        return $this;
    }

    /**
     * @param mixed $reservationDetails
     * @return Creator
     */
    public function setReservationDetails($reservationDetails)
    {
        $this->reservationDetails = $reservationDetails;
        return $this;
    }
}