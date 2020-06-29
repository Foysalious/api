<?php namespace Sheba\Business\Procurement;

use App\Jobs\Business\SendRFQCreateNotificationToPartners;
use App\Models\Bid;
use App\Models\Partner;
use App\Models\Procurement;
use App\Models\ProcurementItem;
use App\Models\Tag;
use App\Sheba\Attachments\Attachments;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\UploadedFile;
use phpDocumentor\Reflection\DocBlock\Description;
use Sheba\Notification\NotificationCreated;
use Sheba\Repositories\Interfaces\ProcurementItemFieldRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementItemRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use DB;

class Creator
{
    use DispatchesJobs;
    private $procurementRepository;
    private $procurementItemRepository;
    private $procurementQuestionRepository;
    private $procurementItemFieldRepository;
    /** @var Attachments */
    private $attachmentManager;
    private $purchaseRequestId;
    private $type;
    private $title;
    private $category;
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
    private $labels;
    /** @var UploadedFile[] */
    private $attachments = [];
    /** @var Procurement $procurement */
    private $procurement;
    private $bid;
    private $createdBy;
    private $sharedTo;

    /**
     * Creator constructor.
     * @param ProcurementRepositoryInterface $procurement_repository
     * @param ProcurementItemRepositoryInterface $procurement_item_repository
     * @param ProcurementItemFieldRepositoryInterface $procurement_item_field_repository
     * @param ProcurementQuestionRepositoryInterface $procurement_question_repository
     * @param Attachments $attachment_manager
     */
    public function __construct(ProcurementRepositoryInterface $procurement_repository,
                                ProcurementItemRepositoryInterface $procurement_item_repository,
                                ProcurementItemFieldRepositoryInterface $procurement_item_field_repository,
                                ProcurementQuestionRepositoryInterface $procurement_question_repository,
                                Attachments $attachment_manager)
    {
        $this->procurementRepository = $procurement_repository;
        $this->procurementItemRepository = $procurement_item_repository;
        $this->procurementQuestionRepository = $procurement_question_repository;
        $this->procurementItemFieldRepository = $procurement_item_field_repository;
        $this->attachmentManager = $attachment_manager;
    }

    public function getProcurement($procurement)
    {
        $this->procurement = $this->procurementRepository->find((int)$procurement);
        return $this;
    }

    public function setBid(Bid $bid)
    {
        $this->bid = $bid;
        return $this;
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

    public function setCategory($category)
    {
        $this->category = $category != 0 ? $category : null;
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
        $this->procurementEndDate = $procurement_end_date ? $procurement_end_date . ' 23:59:59' : null;
        return $this;
    }

    public function setNumberOfParticipants($number_of_participants)
    {
        $this->numberOfParticipants = $number_of_participants;
        return $this;
    }

    public function setLastDateOfSubmission($last_date_of_submission)
    {
        $this->lastDateOfSubmission = $last_date_of_submission . ' 23:59:59';
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

    public function setLabels($labels)
    {
        $this->labels = $labels;
        $this->labels = $this->labels ? json_decode($this->labels, true) : [];
        return $this;
    }

    /**
     * @param $attachments UploadedFile[]
     * @return $this
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * @param $created_by
     * @return $this
     */
    public function setCreatedBy($created_by)
    {
        $this->createdBy = $created_by;
        return $this;
    }

    /**
     * @param $sharing_to
     * @return $this
     */
    public function setSharingTo($sharing_to)
    {
        $this->sharedTo = $sharing_to;
        return $this;
    }

    /**
     * @return $this
     */
    public function getBid()
    {
        $this->bid = $this->procurement->getActiveBid();
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
                $this->createTags($procurement);
                $this->createAttachments($procurement);
                foreach ($this->items as $item_fields) {
                    /** @var ProcurementItem $procurement_item */
                    $procurement_item = $this->createProcurementItem($procurement, $item_fields->item_type);
                    $this->makeItemFields($procurement_item, $item_fields->fields);
                    $this->procurementItemFieldRepository->createMany($this->procurementItemFieldData);
                }
                $this->makeQuestion($procurement);
                $this->procurementQuestionRepository->createMany($this->procurementQuestionData);
                if ($procurement->is_published) $this->sendNotification($procurement);
            });
        } catch (QueryException $e) {
            throw $e;
        }
        return $procurement;
    }

    /**
     * @param $procurement
     * @param $item_type
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createProcurementItem($procurement, $item_type)
    {
        return $this->procurementItemRepository->create([
            'procurement_id' => $procurement->id,
            'type' => $item_type
        ]);
    }

    private function makeProcurementData()
    {
        $this->procurementData = [
            'long_description' => $this->longDescription,
            'procurement_start_date' => $this->procurementStartDate,
            'procurement_end_date' => $this->procurementEndDate,
            'last_date_of_submission' => $this->lastDateOfSubmission,
            'number_of_participants' => $this->numberOfParticipants,
            'shared_to' => $this->sharedTo,

            'owner_type' => get_class($this->owner),
            'owner_id' => $this->owner->id,

            'payment_options' => $this->paymentOptions,

            'title' => $this->title,
            'category_id' => $this->category,
            'is_published' => $this->isPublished ? (int)$this->isPublished : 0,
            'published_at' => $this->isPublished ? Carbon::now() : '',

            'purchase_request_id' => $this->purchaseRequestId,
            'type' => count($this->items) > 0 ? Type::ADVANCED : Type::BASIC,
            'estimated_price' => $this->estimatedPrice,
            'order_start_date' => $this->orderStartDate,
            'order_end_date' => $this->orderEndDate,
            'interview_date' => $this->interviewDate
        ];
    }

    /**
     * @param ProcurementItem $procurement_item
     * @param $fields
     */
    private function makeItemFields(ProcurementItem $procurement_item, $fields)
    {
        $this->procurementItemFieldData = [];
        foreach ($fields as $field) {
            $is_required = isset($field->is_required) ? $field->is_required : 1;
            $options = isset($field->options) ? $field->options : [];
            $unit = isset($field->unit) ? $field->unit : null;
            array_push($this->procurementItemFieldData, [
                'title' => $field->title,
                'short_description' => isset($field->short_description) ? $field->short_description : '',
                'input_type' => isset($field->type) ? $field->type : null,
                'result' => isset($field->result) ? $field->result : null,
                'procurement_item_id' => $procurement_item->id,
                'variables' => json_encode(['is_required' => $is_required, 'options' => $options, 'unit' => $unit])
            ]);
        }
    }

    /**
     * @param $procurement
     */
    private function createTags($procurement)
    {
        $tags = Tag::sync($this->labels, get_class($procurement));
        $procurement->tags()->sync($tags);
    }

    /**
     * @param Procurement $procurement
     */
    private function createAttachments(Procurement $procurement)
    {
        foreach ($this->attachments as $attachment) {
            $this->attachmentManager->setAttachableModel($procurement)
                ->setCreatedBy($this->createdBy)
                ->setFile($attachment)
                ->store();
        }
    }

    /**
     * @param Procurement $procurement
     */
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

    /**
     * @param Procurement $procurement
     */
    public function changeStatus(Procurement $procurement)
    {
        $this->procurementData = [
            'is_published' => $this->isPublished ? (int)$this->isPublished : 0,
            'published_at' => $this->isPublished ? Carbon::now() : '',
            'shared_to' => $this->sharedTo
        ];
        $this->procurementRepository->update($procurement, $this->procurementData);
        if ($this->isPublished) $this->sendNotification($procurement);
    }

    /**
     * @param Procurement $procurement
     */
    private function sendNotification(Procurement $procurement)
    {
        dispatch(new SendRFQCreateNotificationToPartners($procurement));
    }
}
