<?php namespace Sheba\Business\Procurement;

class RequestHandler
{
    private $longDescription;
    private $procurementStartDate;
    private $procurementEndDate;
    private $numberOfParticipants;
    private $lastDateOfSubmission;
    private $paymentOptions;

    /**
     * @param $long_description
     * @return $this
     */
    public function setLongDescription($long_description)
    {
        $this->longDescription = $long_description ? $long_description : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * @param $procurement_start_date
     * @return $this
     */
    public function setProcurementStartDate($procurement_start_date)
    {
        $this->procurementStartDate = $procurement_start_date ? $procurement_start_date : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProcurementStartDate()
    {
        return $this->procurementStartDate;
    }

    /**
     * @param $procurement_end_date
     * @return $this
     */
    public function setProcurementEndDate($procurement_end_date)
    {
        $this->procurementEndDate = $procurement_end_date ? $procurement_end_date . ' 23:59:59' : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProcurementEndDate()
    {
        return $this->procurementEndDate;
    }

    /**
     * @param $number_of_participants
     * @return $this
     */
    public function setNumberOfParticipants($number_of_participants)
    {
        $this->numberOfParticipants = $number_of_participants;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumberOfParticipants()
    {
        return $this->numberOfParticipants;
    }

    /**
     * @param $last_date_of_submission
     * @return $this
     */
    public function setLastDateOfSubmission($last_date_of_submission)
    {
        $this->lastDateOfSubmission = $last_date_of_submission ? $last_date_of_submission . ' 23:59:59' : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastDateOfSubmission()
    {
        return $this->lastDateOfSubmission;
    }

    /**
     * @param $payment_options
     * @return $this
     */
    public function setPaymentOptions($payment_options)
    {
        $this->paymentOptions = $payment_options;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentOptions()
    {
        return $this->paymentOptions;
    }
}