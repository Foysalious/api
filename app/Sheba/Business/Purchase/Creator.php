<?php namespace Sheba\Business\Purchase;

use App\Models\PurchaseRequest;
use Illuminate\Database\QueryException;

use DB;
use Sheba\Repositories\Interfaces\PurchaseRequestItemRepositoryInterface;
use Sheba\Repositories\Interfaces\PurchaseRequestQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\PurchaseRequestRepositoryInterface;

class Creator
{
    private $purchaseRequestRepository;
    private $purchaseRequestItemRepository;
    private $purchaseRequestQuestionRepository;
    private $type;
    private $estimatedPrice;
    private $estimatedDate;
    private $business;
    private $member;
    private $title;
    private $longDescription;
    private $items;
    private $questions;
    private $purchaseRequestData;
    private $purchaseRequestItemData;
    private $purchaseRequestQuestionData;
    private $formTemplateId;

    /**
     * Creator constructor.
     *
     * @param PurchaseRequestRepositoryInterface $purchaseRequest_repository
     * @param PurchaseRequestItemRepositoryInterface $purchaseRequest_item_repository
     * @param PurchaseRequestQuestionRepositoryInterface $purchaseRequest_question_repository
     */
    public function __construct(PurchaseRequestRepositoryInterface $purchaseRequest_repository,
                                PurchaseRequestItemRepositoryInterface $purchaseRequest_item_repository,
                                PurchaseRequestQuestionRepositoryInterface $purchaseRequest_question_repository)
    {
        $this->purchaseRequestRepository = $purchaseRequest_repository;
        $this->purchaseRequestItemRepository = $purchaseRequest_item_repository;
        $this->purchaseRequestQuestionRepository = $purchaseRequest_question_repository;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    public function setFormTemplateId($form_template_id)
    {
        $this->formTemplateId = $form_template_id ? : null;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setEstimatedPrice($estimated_price)
    {
        $this->estimatedPrice = $estimated_price;
        return $this;
    }

    public function setEstimatedDate($estimated_date)
    {
        $this->estimatedDate = $estimated_date;
        return $this;
    }

    public function setLongDescription($long_description)
    {
        $this->longDescription = $long_description;
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
        $this->makePurchaseRequestData();
        $purchaseRequest = null;
        try {
            DB::transaction(function () use (&$purchaseRequest) {
                /** @var PurchaseRequest $purchaseRequest */
                $purchaseRequest = $this->purchaseRequestRepository->create($this->purchaseRequestData);
                $this->makeItem($purchaseRequest);
                $this->purchaseRequestItemRepository->createMany($this->purchaseRequestItemData);
                $this->makeQuestion($purchaseRequest);
                $this->purchaseRequestQuestionRepository->createMany($this->purchaseRequestQuestionData);
            });
        } catch (QueryException $e) {
            throw $e;
        }

        return $purchaseRequest;
    }

    private function makePurchaseRequestData()
    {
        $this->purchaseRequestData = [
            'form_template_id' => $this->formTemplateId,
            'title' => $this->title,
            'long_description' => $this->longDescription,
            'type' => $this->type,
            'estimated_price' => $this->estimatedPrice,
            'business_id' => $this->business->id,
            'member_id' => $this->member->id
        ];
    }

    /**
     * @param PurchaseRequest $purchaseRequest
     */
    private function makeItem(PurchaseRequest $purchaseRequest)
    {
        $this->purchaseRequestItemData = [];
        $items = json_decode($this->items);
        foreach ($items as $item) {
            array_push($this->purchaseRequestItemData, [
                'title' => $item->title,
                'short_description' => $item->short_description,
                'long_description' => $item->instructions,
                'input_type' => $item->type,
                'purchase_request_id' => $purchaseRequest->id,
                'variables' => json_encode(['is_required' => $item->is_required]),
            ]);
        }
    }

    /**
     * @param PurchaseRequest $purchaseRequest
     */
    private function makeQuestion(PurchaseRequest $purchaseRequest)
    {
        $this->purchaseRequestQuestionData = [];
        $questions = json_decode($this->questions);
        foreach ($questions as $question) {
            array_push($this->purchaseRequestQuestionData, [
                'title' => $question->title,
                'short_description' => $question->short_description,
                'long_description' => $question->instructions,
                'input_type' => $question->type,
                'purchase_request_id' => $purchaseRequest->id,
                'variables' => json_encode(['is_required' => $question->is_required]),
            ]);
        }
    }
}