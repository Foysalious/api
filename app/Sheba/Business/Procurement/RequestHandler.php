<?php namespace Sheba\Business\Procurement;

class RequestHandler
{
    private $procurementStartDate;
    private $procurementEndDate;
    private $numberOfParticipants;
    private $paymentOptions;
    private $workOrder;
    private $category;
    private $tags;

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

    /**
     * @param $work_order
     * @return $this
     */
    public function setWorkOrder($work_order)
    {
        $this->workOrder = $work_order;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWorkOrder()
    {
        return $this->workOrder;
    }

    public function setCategory($category)
    {
        $this->category = $category ? $category : null;
        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
        $this->tags = $this->tags ? json_decode($this->tags,true) : [];
        return $this;
    }

    public function getTags()
    {
        return $this->tags;
    }
}