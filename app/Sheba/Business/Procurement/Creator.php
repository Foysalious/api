<?php namespace Sheba\Business\Procurement;


use App\Models\PurchaseRequest;
use Sheba\Repositories\Interfaces\ProcurementItemRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class Creator
{
    private $procurementRepository;
    private $procurementItemRepository;
    private $procurementQuestionRepository;
    /** @var PurchaseRequest */
    private $purchaseRequest;
    private $type;
    private $estimatedPrice;
    private $longDescription;
    private $jobStartDate;
    private $jobEndDate;
    private $interviewDate;
    private $publishStartDate;
    private $publishEndDate;

    public function __construct(ProcurementRepositoryInterface $procurement_repository, ProcurementItemRepositoryInterface $procurement_item_repository, ProcurementQuestionRepositoryInterface $procurement_question_repository)
    {
        $this->procurementRepository = $procurement_repository;
        $this->procurementItemRepository = $procurement_item_repository;
        $this->procurementQuestionRepository = $procurement_question_repository;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setPurchaseRequest($purchase_request)
    {
        $this->purchaseRequest = $purchase_request;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function estimatedPrice($estimated_price)
    {
        $this->estimatedPrice = $estimated_price;
        return $this;
    }

    public function setLongDescription($long_description)
    {
        $this->longDescription = $long_description;
        return $this;
    }

    public function setJobStartDate($job_start_date)
    {
        $this->jobStartDate = $job_start_date;
        return $this;
    }

    public function setJobEndDate($job_end_date)
    {
        $this->jobEndDate = $job_end_date;
        return $this;
    }

    public function setInterviewDate($interview_date)
    {
        $this->interviewDate = $interview_date;
        return $this;
    }

    public function setPublishStartDate($publish_start_date)
    {
        $this->publishStartDate = $publish_start_date;
        return $this;
    }

    public function setPublishEndDate($publish_end_date)
    {
        $this->publishEndDate = $publish_end_date;
        return $this;
    }
}