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

    public function setLabels($labels)
    {
        $this->labels = $labels;
        $this->labels = $this->labels ? explode(', ', $this->labels) : [];
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

    public function setCreatedBy($created_by)
    {
        $this->createdBy = $created_by;
        return $this;
    }

    public function setSharingTo($sharing_to)
    {
        $this->sharedTo = $sharing_to;
        return $this;
    }

    public function getBid()
    {
        $this->bid = $this->procurement->getActiveBid();
        return $this;
    }

    public function create()
    {
        $this->makeProcurementData();
        dd(count($this->items));
        $procurement = null;
        try {
            DB::transaction(function () use (&$procurement) {
                /** @var Procurement $procurement */
                $procurement = $this->procurementRepository->create($this->procurementData);
                $this->createTags($procurement);
                $this->createAttachments($procurement);
                foreach ($this->items as $item_fields) {
                    /** @var ProcurementItem $procurement_item */
                    $procurement_item = $this->procurementItemRepository->create([
                        'procurement_id' => $procurement->id,
                        'type' => $item_fields->item_type
                    ]);
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
            'is_published' => $this->isPublished ? (int)$this->isPublished : 0,
            'published_at' => $this->isPublished ? Carbon::now() : '',

            'purchase_request_id' => $this->purchaseRequestId,
            'type' => count($this->items) > 0 ? 'advanced' : 'basic',
            'estimated_price' => $this->estimatedPrice,
            'order_start_date' => $this->orderStartDate,
            'order_end_date' => $this->orderEndDate,
            'interview_date' => $this->interviewDate,
        ];
    }

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
                'input_type' => $field->type,
                'result' => isset($field->result) ? $field->result : null,
                'procurement_item_id' => $procurement_item->id,
                'variables' => json_encode(['is_required' => $is_required, 'options' => $options, 'unit' => $unit])
            ]);
        }
    }

    private function createTags($procurement)
    {
        $tags = Tag::sync($this->labels, get_class($procurement));
        $procurement->tags()->sync($tags);
    }

    private function createAttachments(Procurement $procurement)
    {
        foreach ($this->attachments as $attachment) {
            $this->attachmentManager->setAttachableModel($procurement)
                ->setCreatedBy($this->createdBy)
                ->setFile($attachment)
                ->store();
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

    public function formatTimeline()
    {
        $payment_requests = $this->procurement->paymentRequests()->with('statusChangeLogs')->get();

        $requests = [];
        $request_logs = [];
        foreach ($payment_requests as $payment_request) {
            $payment_request_logs = $payment_request->statusChangeLogs->isEmpty() ? null : $payment_request->statusChangeLogs;
            if ($payment_request_logs) {
                foreach ($payment_request_logs as $log) {
                    array_push($request_logs, [
                        'created_at' => $log->created_at->toDateTimeString(),
                        'time' => $log->created_at->format('h.i A'),
                        'date' => $log->created_at->format('Y-m-d'),
                        'log' => 'Status Updated From ' . $log->from_status . ' To ' . $log->to_status
                    ]);
                }
            }
            array_push($requests, [
                'created_at' => $payment_request->created_at->toDateTimeString(),
                'time' => $payment_request->created_at->format('h.i A'),
                'date' => $payment_request->created_at->format('Y-m-d'),
                'log' => 'This Payment Request: #' . $payment_request->id . ' Is ' . $payment_request->status
            ]);
        }
        $bid_status_change_log = $this->bid->statusChangeLogs()->where('to_status', 'awarded')->first();
        $data = [
            'created_at' => $bid_status_change_log->created_at->toDateTimeString(),
            'time' => $bid_status_change_log->created_at->format('h.i A'),
            'date' => $bid_status_change_log->created_at->format('Y-m-d'),
            'log' => 'Hired ' . $this->bid->bidder->name . ' and Status Updated From ' . $bid_status_change_log->from_status . ' To ' . $bid_status_change_log->to_status
        ];

        $order_time_lines = collect(array_merge([$data], $requests, $request_logs))->sortByDesc('created_at')->groupBy('date');
        $order_time_line = [];
        foreach ($order_time_lines as $key => $time_lines) {
            array_push($order_time_line, [
                'date' => Carbon::parse($key)->format('d M'),
                'year' => Carbon::parse($key)->format('Y'),
                'logs' => $time_lines,
            ]);
        }
        return $order_time_line;
    }

    public function changeStatus(Procurement $procurement)
    {
        $this->procurementData = [
            'is_published' => $this->isPublished ? (int)$this->isPublished : 0,
            'published_at' => $this->isPublished ? Carbon::now() : ''
        ];
        $this->procurementRepository->update($procurement, $this->procurementData);
        if ($this->isPublished) $this->sendNotification($procurement);
    }

    public function formatData()
    {
        $bid_price_quotations = null;
        if ($this->procurement->isAdvanced())
            $bid_price_quotations = $this->generateBidItemData();
        return [
            'procurement_id' => $this->procurement->id,
            'procurement_title' => $this->procurement->title,
            'procurement_status' => $this->procurement->status,
            'color' => constants('PROCUREMENT_ORDER_STATUSES_COLOR')[$this->procurement->status],
            'procurement_start_date' => Carbon::parse($this->procurement->procurement_start_date)->format('d/m/y'),
            'procurement_end_date' => Carbon::parse($this->procurement->procurement_end_date)->format('d/m/y'),
            'procurement_type' => $this->procurement->type,
            'procurement_additional_info' => $this->procurement->long_description,
            'vendor' => [
                'name' => $this->bid->bidder->name,
                'logo' => $this->bid->bidder->logo,
                'contact_person' => $this->bid->bidder->getContactPerson(),
                'mobile' => $this->bid->bidder->getMobile(),
                'address' => $this->bid->bidder->address,
                'rating' => round($this->bid->bidder->reviews->avg('rating'), 2),
                'total_rating' => $this->bid->bidder->reviews->count()
            ],
            'bid_id' => $this->bid->id,
            'bid_price' => $this->bid->price,
            'bid_price_quotations' => $bid_price_quotations
        ];
    }

    private function generateBidItemData()
    {
        $item_type = $this->bid->items->where('type', 'price_quotation')->first();
        $item_fields = [];
        foreach ($item_type->fields as $field) {
            $unit = $field->variables ? json_decode($field->variables)->unit ? json_decode($field->variables)->unit : 0 : 0;
            array_push($item_fields, [
                'id' => $field->id,
                'title' => $field->title,
                'short_description' => $field->short_description,
                'unit' => $unit,
                'unit_price' => number_format($field->result / $unit, 2),
                'total_price' => $field->result,
            ]);
        }
        return $item_fields;
    }

    private function sendNotification(Procurement $procurement)
    {
        dispatch(new SendRFQCreateNotificationToPartners($procurement));
    }
}
