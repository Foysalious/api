<?php namespace Sheba\Transport\Bus\Order;

use Carbon\Carbon;
use Sheba\Transport\TransportAgent;

class TransportTicketRequest
{
    private $agentType;
    private $agentId;
    private $reserverName;
    private $reserverMobile;
    private $reserverEmail;
    private $reserverGender;
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
    private $departureStationId;
    private $arrivalStationId;
    private $reservationDetails;
    private $coachId;
    private $seatListId;
    private $boardingPoint;
    private $droppingPoint;

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
     * @return TransportTicketRequest
     */
    public function setReserverName($reserverName)
    {
        $this->reserverName = $reserverName;
        return $this;
    }

    /**
     * @param $reserver_mobile
     * @return TransportTicketRequest
     */
    public function setReserverMobile($reserver_mobile)
    {
        $this->reserverMobile = $reserver_mobile;
        return $this;
    }

    /**
     * @param $reserver_email
     * @return TransportTicketRequest
     */
    public function setReserverEmail($reserver_email)
    {
        $this->reserverEmail = $reserver_email;
        return $this;
    }

    /**
     * @param $vendor_id
     * @return TransportTicketRequest
     */
    public function setVendorId($vendor_id)
    {
        $this->vendorId = $vendor_id;
        return $this;
    }

    /**
     * @param mixed $status
     * @return TransportTicketRequest
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return TransportTicketRequest
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $discount
     * @return TransportTicketRequest
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @param $discount_percent
     * @return TransportTicketRequest
     */
    public function setDiscountPercent($discount_percent)
    {
        $this->discountPercent = $discount_percent;
        return $this;
    }

    /**
     * @param $sheba_contribution
     * @return TransportTicketRequest
     */
    public function setShebaContribution($sheba_contribution)
    {
        $this->shebaContribution = $sheba_contribution;
        return $this;
    }

    /**
     * @param $vendor_contribution
     * @return TransportTicketRequest
     */
    public function setVendorContribution($vendor_contribution)
    {
        $this->vendorContribution = $vendor_contribution;
        return $this;
    }

    /**
     * @param mixed $transaction_id
     * @return TransportTicketRequest
     */
    public function setTransactionId($transaction_id)
    {
        $this->transactionId = $transaction_id;
        return $this;
    }

    /**
     * @param mixed $journey_date
     * @return TransportTicketRequest
     */
    public function setJourneyDate($journey_date)
    {
        $this->journeyDate = $journey_date;
        return $this;
    }

    /**
     * @param mixed $departure_time
     * @return TransportTicketRequest
     */
    public function setDepartureTime($departure_time)
    {
        $this->departureTime = Carbon::parse($departure_time)->toTimeString();
        return $this;
    }

    /**
     * @param mixed $arrival_time
     * @return TransportTicketRequest
     */
    public function setArrivalTime($arrival_time)
    {
        $this->arrivalTime = Carbon::parse($arrival_time)->toTimeString();
        return $this;
    }

    /**
     * @param mixed $departure_station_name
     * @return TransportTicketRequest
     */
    public function setDepartureStationName($departure_station_name)
    {
        $this->departureStationName = $departure_station_name;
        return $this;
    }

    /**
     * @param mixed $arrival_station_name
     * @return TransportTicketRequest
     */
    public function setArrivalStationName($arrival_station_name)
    {
        $this->arrivalStationName = $arrival_station_name;
        return $this;
    }

    /**
     * @param mixed $reservation_details
     * @return TransportTicketRequest
     */
    public function setReservationDetails($reservation_details)
    {
        $this->reservationDetails = $reservation_details;
        return $this;
    }

    /**
     * @param $coach_id
     * @return $this
     */
    public function setCoachId($coach_id)
    {
        $this->coachId = $coach_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCoachId()
    {
        return $this->coachId;
    }

    /**
     * @param $departure_station_id
     * @return TransportTicketRequest
     */
    public function setDepartureStationId($departure_station_id)
    {
        $this->departureStationId = $departure_station_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArrivalStationId()
    {
        return $this->arrivalStationId;
    }

    /**
     * @param $arrival_station_id
     * @return TransportTicketRequest
     */
    public function setArrivalStationId($arrival_station_id)
    {
        $this->arrivalStationId = $arrival_station_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDepartureStationId()
    {
        return $this->departureStationId;
    }

    /**
     * @return mixed
     */
    public function getReserverName()
    {
        return $this->reserverName;
    }

    /**
     * @return mixed
     */
    public function getReserverMobile()
    {
        return $this->reserverMobile;
    }

    /**
     * @return mixed
     */
    public function getReserverEmail()
    {
        return $this->reserverEmail;
    }

    /**
     * @return mixed
     */
    public function getReserverGender()
    {
        return $this->reserverGender;
    }

    /**
     * @param $reserver_gender
     * @return TransportTicketRequest
     */
    public function setReserverGender($reserver_gender)
    {
        $this->reserverGender = $reserver_gender;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSeatIdList()
    {
        return $this->seatListId;
    }

    /**
     * @param $seat_list_id
     * @return TransportTicketRequest
     */
    public function setSeatIdList($seat_list_id)
    {
        $this->seatListId = explode(',', $seat_list_id);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBoardingPoint()
    {
        return $this->boardingPoint;
    }

    /**
     * @param $boarding_point
     * @return TransportTicketRequest
     */
    public function setBoardingPoint($boarding_point)
    {
        $this->boardingPoint = $boarding_point;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDroppingPoint()
    {
        return $this->droppingPoint;
    }

    /**
     * @param $dropping_point
     * @return TransportTicketRequest
     */
    public function setDroppingPoint($dropping_point)
    {
        $this->droppingPoint = $dropping_point;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAgentType()
    {
        return $this->agentType;
    }

    /**
     * @return mixed
     */
    public function getAgentId()
    {
        return $this->agentId;
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->vendorId;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @return mixed
     */
    public function getVendorContribution()
    {
        return $this->vendorContribution;
    }

    /**
     * @return mixed
     */
    public function getShebaContribution()
    {
        return $this->shebaContribution;
    }

    /**
     * @return mixed
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return mixed
     */
    public function getJourneyDate()
    {
        return $this->journeyDate;
    }

    /**
     * @return mixed
     */
    public function getArrivalTime()
    {
        return $this->arrivalTime;
    }

    /**
     * @return mixed
     */
    public function getDepartureTime()
    {
        return $this->departureTime;
    }

    /**
     * @return mixed
     */
    public function getDepartureStationName()
    {
        return $this->departureStationName;
    }

    /**
     * @return mixed
     */
    public function getArrivalStationName()
    {
        return $this->arrivalStationName;
    }

    /**
     * @return mixed
     */
    public function getReservationDetails()
    {
        return $this->reservationDetails;
    }
}