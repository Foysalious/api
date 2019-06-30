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

    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    public function setQuestions($questions)
    {
        $this->questions = $questions;
        return $this;
    }

    public function create()
    {
        $this->makeProcurementData();
        $procurement = null;
        $items = json_decode($this->items);
        try {
            DB::transaction(function () use (&$procurement, $items) {
                /** @var Procurement $procurement */
                $procurement = $this->procurementRepository->create($this->procurementData);
                foreach ($items as $fields) {
                    /** @var ProcurementItem $procurement_item */
                    $procurement_item = $this->procurementItemRepository->create(['procurement_id' => $procurement->id]);
                    $this->makeItemFields($procurement_item, $fields);
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
        ];
    }

    private function makeItemFields(ProcurementItem $procurement_item, $fields)
    {
        $this->procurementItemFieldData = [];
        foreach ($fields as $field) {
            array_push($this->procurementItemFieldData, [
                'title' => $field->title,
                'short_description' => $field->short_description,
                'long_description' => $field->instructions,
                'input_type' => $field->type,
                'procurement_item_id' => $procurement_item->id,
                'variables' => json_encode(['is_required' => $field->is_required]),
                'result' => $field->result
            ]);
        }
    }

    private function makeQuestion(Procurement $procurement)
    {
        $this->procurementQuestionData = [];
        $questions = json_decode($this->questions);
        foreach ($questions as $question) {
            array_push($this->procurementQuestionData, [
                'title' => $question->title,
                'short_description' => $question->short_description,
                'long_description' => $question->instructions,
                'input_type' => $question->type,
                'procurement_id' => $procurement->id,
                'variables' => json_encode(['is_required' => $question->is_required]),
            ]);
        }
    }
}