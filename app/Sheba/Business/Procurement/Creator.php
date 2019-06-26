<?php namespace Sheba\Business\Procurement;


use App\Models\Procurement;
use Illuminate\Database\QueryException;
use Sheba\Repositories\Interfaces\ProcurementItemRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use DB;

class Creator
{
    private $procurementRepository;
    private $procurementItemRepository;
    private $procurementQuestionRepository;
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
    private $procurementItemData;
    private $procurementQuestionData;

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
        $this->estimatedPrice = $estimated_price;
        return $this;
    }

    public function setLongDescription($long_description)
    {
        $this->longDescription = $long_description;
        return $this;
    }

    public function setOrderStartDate($order_start_date)
    {
        $this->orderStartDate = $order_start_date;
        return $this;
    }

    public function setOrderEndDate($order_end_date)
    {
        $this->orderEndDate = $order_end_date;
        return $this;
    }

    public function setInterviewDate($interview_date)
    {
        $this->interviewDate = $interview_date;
        return $this;
    }

    public function setProcurementStartDate($procurement_start_date)
    {
        $this->procurementStartDate = $procurement_start_date;
        return $this;
    }

    public function setProcurementEndDate($procurement_end_date)
    {
        $this->procurementEndDate = $procurement_end_date;
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
        try {
            DB::transaction(function () use (&$procurement) {
                /** @var Procurement $procurement */
                $procurement = $this->procurementRepository->create($this->procurementData);
                $this->makeItem($procurement);
                $this->procurementItemRepository->createMany($this->procurementItemData);
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

    private function makeItem(Procurement $procurement)
    {
        $this->procurementItemData = [];
        $items = json_decode($this->items);
        foreach ($items as $item) {
            array_push($this->procurementItemData, [
                'title' => $item->title,
                'short_description' => $item->short_description,
                'long_description' => $item->instructions,
                'input_type' => $item->type,
                'procurement_id' => $procurement->id,
                'variables' => json_encode(['is_required' => $item->is_required]),
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