<?php namespace Sheba\Business\Procurement;

use App\Models\Procurement;
use App\Models\ProcurementItem;
use Illuminate\Database\QueryException;
use Sheba\Repositories\Interfaces\ProcurementItemFieldRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementItemRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use DB;

class Creator
{
    private $procurementRepository;
    private $procurementItemRepository;
    private $procurementQuestionRepository;
    private $procurementItemFieldRepository;
    private $purchaseRequestId;
    private $type;
    private $title;
    private $estimatedPrice;
    private $longDescription;
    private $orderStartDate;
    private $orderEndDate;
    private $interviewDate;
    private $procurementStartDate;
    private $procurementEndDate;
    private $owner;
    private $items;
    private $questions;
    private $procurementData;
    private $procurementItemFieldData;
    private $procurementQuestionData;
    private $numberOfParticipants;
    private $lastDateOfSubmission;
    private $paymentOptions;
    private $isPublished;

    public function __construct(ProcurementRepositoryInterface $procurement_repository, ProcurementItemRepositoryInterface $procurement_item_repository, ProcurementItemFieldRepositoryInterface $procurement_item_field_repository, ProcurementQuestionRepositoryInterface $procurement_question_repository)
    {
        $this->procurementRepository = $procurement_repository;
        $this->procurementItemRepository = $procurement_item_repository;
        $this->procurementQuestionRepository = $procurement_question_repository;
        $this->procurementItemFieldRepository = $procurement_item_field_repository;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    public function setPurchaseRequest($purchase_request_id)
    {
        $this->purchaseRequestId = $purchase_request_id ? $purchase_request_id : null;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function estimatedPrice($estimated_price)
    {
        $this->estimatedPrice = $estimated_price ? $estimated_price : null;
        return $this;
    }

    public function setLongDescription($long_description)
    {
        $this->longDescription = $long_description ? $long_description : null;
        return $this;
    }

    public function setOrderStartDate($order_start_date)
    {
        $this->orderStartDate = $order_start_date ? $order_start_date : null;
        return $this;
    }

    public function setOrderEndDate($order_end_date)
    {
        $this->orderEndDate = $order_end_date ? $order_end_date : null;
        return $this;
    }

    public function setInterviewDate($interview_date)
    {
        $this->interviewDate = $interview_date ? $interview_date : null;
        return $this;
    }

    public function setProcurementStartDate($procurement_start_date)
    {
        $this->procurementStartDate = $procurement_start_date ? $procurement_start_date : null;
        return $this;
    }

    public function setProcurementEndDate($procurement_end_date)
    {
        $this->procurementEndDate = $procurement_end_date ? $procurement_end_date : null;
        return $this;
    }

    public function setNumberOfParticipants($number_of_participants)
    {
        $this->numberOfParticipants = $number_of_participants;
        return $this;
    }

    public function setLastDateOfSubmission($last_date_of_submission)
    {
        $this->lastDateOfSubmission = $last_date_of_submission;
        return $this;
    }

    public function setPaymentOptions($payment_options)
    {
        $this->paymentOptions = $payment_options;
        return $this;
    }

    public function setItems($items)
    {
        $this->items = json_decode($items);
        $this->items = $this->items == null ? [] : $this->items;
        return $this;
    }

    public function setQuestions($questions)
    {
        $this->questions = json_decode($questions);
        $this->questions = $this->questions == null ? [] : $this->questions;
        return $this;
    }

    public function setIsPublished($is_published)
    {
        $this->isPublished = $is_published;
        return $this;
    }

    public function create()
    {
        $this->makeProcurementData();
        $procurement = null;
        try {
            DB::transaction(function () use (&$procurement) {
                /** @var Procurement $procurement */
                $procurement = $this->procurementRepository->create($this->procurementData);
                foreach ($this->items as $item_fields) {
                    /** @var ProcurementItem $procurement_item */
                    $procurement_item = $this->procurementItemRepository->create(['procurement_id' => $procurement->id, 'type' => $item_fields->item_type]);
                    $this->makeItemFields($procurement_item, $item_fields->fields);
                    $this->procurementItemFieldRepository->createMany($this->procurementItemFieldData);
                }

                $this->makeQuestion($procurement);
                $this->procurementQuestionRepository->createMany($this->procurementQuestionData);
            });
        } catch (QueryException $e) {
            throw $e;
        }

        return $procurement;
    }

    private function makeProcurementData()
    {
        $this->procurementData = [
            'purchase_request_id' => $this->purchaseRequestId,
            'title' => $this->title,
            'long_description' => $this->longDescription,
            'type' => $this->type,
            'estimated_price' => $this->estimatedPrice,
            'order_start_date' => $this->orderStartDate,
            'order_end_date' => $this->orderEndDate,
            'interview_date' => $this->interviewDate,
            'procurement_start_date' => $this->procurementStartDate,
            'procurement_end_date' => $this->procurementEndDate,
            'owner_type' => get_class($this->owner),
            'owner_id' => $this->owner->id,
            'number_of_participants' => $this->numberOfParticipants,
            'last_date_of_submission' => $this->lastDateOfSubmission,
            'payment_options' => $this->paymentOptions,
        ];
    }

    private function makeItemFields(ProcurementItem $procurement_item, $fields)
    {
        $this->procurementItemFieldData = [];
        foreach ($fields as $field) {
            array_push($this->procurementItemFieldData, [
                'title' => $field->title,
                'input_type' => $field->type,
                'result' => $field->result,
                'procurement_item_id' => $procurement_item->id,
            ]);
        }
    }

    private function makeQuestion(Procurement $procurement)
    {
        $this->procurementQuestionData = [];
        foreach ($this->questions as $question) {
            array_push($this->procurementQuestionData, [
                'title' => $question->title,
                'short_description' => isset($question->short_description) ? $question->short_description : '',
                'long_description' => isset($question->instructions) ? $question->instructions : '',
                'input_type' => $question->type,
                'procurement_id' => $procurement->id,
                'variables' => isset($question->is_required) ? json_encode(['is_required' => $question->is_required]) : '',
            ]);
        }
    }
}