<?php namespace Sheba\Business\Purchase;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Database\QueryException;

use DB;
use Sheba\Repositories\Interfaces\PurchaseRequestItemFieldRepositoryInterface;
use Sheba\Repositories\Interfaces\PurchaseRequestItemRepositoryInterface;
use Sheba\Repositories\Interfaces\PurchaseRequestQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\PurchaseRequestRepositoryInterface;

class Creator
{
    private $purchaseRequestRepository;
    private $purchaseRequestItemRepository;
    private $purchaseRequestItemFieldRepository;
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
    private $purchaseRequestItemFieldData;

    /**
     * Creator constructor.
     *
     * @param PurchaseRequestRepositoryInterface $purchaseRequest_repository
     * @param PurchaseRequestItemRepositoryInterface $purchaseRequest_item_repository
     * @param PurchaseRequestItemFieldRepositoryInterface $purchaseRequest_item_field_repository
     * @param PurchaseRequestQuestionRepositoryInterface $purchaseRequest_question_repository
     */
    public function __construct(PurchaseRequestRepositoryInterface $purchaseRequest_repository,
                                PurchaseRequestItemRepositoryInterface $purchaseRequest_item_repository,
                                PurchaseRequestItemFieldRepositoryInterface $purchaseRequest_item_field_repository,
                                PurchaseRequestQuestionRepositoryInterface $purchaseRequest_question_repository)
    {
        $this->purchaseRequestRepository = $purchaseRequest_repository;
        $this->purchaseRequestItemRepository = $purchaseRequest_item_repository;
        $this->purchaseRequestItemFieldRepository = $purchaseRequest_item_field_repository;
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
        $purchase_request = null;
        try {
            DB::transaction(function () use (&$purchase_request) {
                /** @var PurchaseRequest $purchase_request */
                $purchase_request = $this->purchaseRequestRepository->create($this->purchaseRequestData);
                $this->makeItem($purchase_request);
                $this->makeQuestion($purchase_request);
                $this->purchaseRequestQuestionRepository->createMany($this->purchaseRequestQuestionData);
            });
        } catch (QueryException $e) {
            throw $e;
        }

        return $purchase_request;
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
     * @param PurchaseRequest $purchase_request
     */
    private function makeItem(PurchaseRequest $purchase_request)
    {
        $this->purchaseRequestItemData = [];
        $items = json_decode($this->items);
        foreach ($items as $item) {
            $this->purchaseRequestItemData = ['purchase_request_id' => $purchase_request->id];
            /** @var PurchaseRequestItem $purchase_request_item */
            $purchase_request_item = $this->purchaseRequestItemRepository->create($this->purchaseRequestItemData);
            $this->makeItemFields($purchase_request_item, $item);
        }
    }

    /**
     * @param PurchaseRequestItem $purchase_request_item
     * @param $item
     */
    private function makeItemFields(PurchaseRequestItem $purchase_request_item, $item)
    {
        $this->purchaseRequestItemFieldData = [];
        foreach ($item as $field) {
            array_push($this->purchaseRequestItemFieldData, [
                'title' => $field->title,
                'short_description' => $field->short_description,
                'long_description' => $field->instructions,
                'input_type' => $field->type,
                'purchase_request_item_id' => $purchase_request_item->id,
                'variables' => json_encode(['is_required' => $field->is_required]),
                'result' => $field->result
            ]);
        }
        $this->purchaseRequestItemFieldRepository->createMany($this->purchaseRequestItemFieldData);
    }

    /**
     * @param PurchaseRequest $purchase_request
     */
    private function makeQuestion(PurchaseRequest $purchase_request)
    {
        $this->purchaseRequestQuestionData = [];
        $questions = json_decode($this->questions);
        foreach ($questions as $question) {
            array_push($this->purchaseRequestQuestionData, [
                'title' => $question->title,
                'short_description' => $question->short_description,
                'long_description' => $question->instructions,
                'input_type' => $question->type,
                'purchase_request_id' => $purchase_request->id,
                'variables' => json_encode(['is_required' => $question->is_required]),
                'result' => $question->result
            ]);
        }
    }
}