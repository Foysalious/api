<?php namespace App\Sheba\Business\Bid;


use App\Models\Bid;
use App\Sheba\Repositories\Business\BidRepository;
use Illuminate\Database\QueryException;
use Sheba\Business\BidStatusChangeLog\Creator;
use Sheba\Notification\NotificationCreated;
use Sheba\Repositories\Interfaces\BidItemFieldRepositoryInterface;
use DB;
use App\Sheba\Business\Procurement\Updater as ProcurementUpdater;

class Updater
{
    private $bidRepository;
    private $bidItemFieldRepository;
    private $isFavourite;
    private $bidData;
    /** @var Bid */
    private $bid;
    private $status;
    private $terms;
    private $policies;
    private $items;
    private $price;
    private $statusLogCreator;
    private $procurementUpdater;
    private $fieldResults;
    private $proposal;

    public function __construct(BidRepository $bid_repository, BidItemFieldRepositoryInterface $bid_item_field_repository, Creator $creator, ProcurementUpdater $procurement_updater)
    {
        $this->bidRepository = $bid_repository;
        $this->bidItemFieldRepository = $bid_item_field_repository;
        $this->statusLogCreator = $creator;
        $this->procurementUpdater = $procurement_updater;
    }

    public function setBid(Bid $bid)
    {
        $this->bid = $bid;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setTerms($terms)
    {
        $this->terms = $terms;
        return $this;
    }

    public function setPolicies($policies)
    {
        $this->policies = $policies;
        return $this;
    }

    public function setItems($item_fields)
    {
        $this->items = collect($item_fields);
        return $this;
    }

    public function setIsFavourite($is_favourite)
    {
        $this->isFavourite = $is_favourite;
        return $this;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }


    public function updateFavourite(Bid $bid)
    {
        $this->bidData = [
            'is_favourite' => $this->isFavourite ? (int)$this->isFavourite : 0,
        ];
        $this->bidRepository->update($bid, $this->bidData);
    }

    public function setFieldResults($field_results)
    {
        $this->fieldResults = collect($field_results);
        return $this;
    }

    public function setProposal($proposal)
    {
        $this->proposal = $proposal;
        return $this;
    }

    public function update()
    {
        try {
            DB::transaction(function () {
                $previous_status = $this->bid->status;
                $this->bidRepository->update($this->bid, ['status' => $this->status, 'proposal' => $this->proposal]);
                foreach ($this->bid->items as $item) {
                    foreach ($item->fields as $field) {
                        $field_result = $this->fieldResults->where('id', $field->id)->first();
                        $this->bidItemFieldRepository->update($field, [
                            'title' => $field->title,
                            'short_description' => $field->short_description,
                            'input_type' => $field->input_type,
                            'variables' => $field->variables,
                            'result' => $field_result ? $field_result->result : null
                        ]);
                    }
                }
                $this->updateBidPrice();
                $this->statusLogCreator->setBid($this->bid)->setPreviousStatus($previous_status)->setStatus($this->status)->create();
                if ($this->status != 'sent') $this->sendVendorParticipatedNotification();
                elseif ($this->status != 'rejected') $this->sendBidRejectedNotification();
                elseif ($this->status != 'accepted') $this->sendBidAcceptedNotification();
            });
        } catch (QueryException $e) {
            throw  $e;
        }
    }

    private function sendVendorParticipatedNotification()
    {
        $message = $this->bid->bidder->name . ' participated on your procurement #' . $this->bid->procurement->id;
        $this->notify($message);
    }

    private function sendBidRejectedNotification()
    {
        $message = $this->bid->bidder->name . ' rejected your hiring request #' . $this->bid->id;
        $this->notify($message);
    }

    private function sendBidAcceptedNotification()
    {
        $message = $this->bid->bidder->name . ' accepted your hiring request #' . $this->bid->id;
        $this->notify($message);
    }

    public function hire()
    {
        try {
            DB::transaction(function () {
                $previous_status = $this->bid->status;
                $this->bidRepository->update($this->bid, ['status' => 'awarded', 'terms' => $this->terms, 'policies' => $this->policies]);
                if ($this->bid->isAdvanced()) {
                    $bid_price_quotation_item = $this->bid->items()->where('type', 'price_quotation')->first();
                    $price_quotation_item = $this->items->where('id', $bid_price_quotation_item->id)->first();
                    $fields = collect($price_quotation_item->fields);
                    foreach ($bid_price_quotation_item->fields as $field) {
                        $field_result = $fields->where('id', $field->id)->first();
                        if ($field_result) {
                            if ($field_result->unit) {
                                $variables = json_decode($field->variables);
                                $variables->unit = $field_result->unit;
                                $variables = json_encode($variables);
                            } else {
                                $variables = null;
                            }
                            $this->bidItemFieldRepository->update($field, [
                                'result' => isset($field_result->result) ? $field_result->result : $field->result,
                                'variables' => $variables ? $variables : $field->variables,
                                'title' => isset($field_result->title) ? $field_result->title : $field->title,
                                'short_description' => isset($field_result->short_description) ? $field_result->short_description : $field->short_description,
                            ]);
                        }
                    }
                }
                $this->updateBidPrice();
                $this->statusLogCreator->setBid($this->bid)->setPreviousStatus($previous_status)->setStatus($this->bid->status)->create();
                $this->sendHiringRequestNotification();
            });
        } catch (QueryException $e) {
            throw  $e;
        }
    }

    public function updateStatus()
    {
        try {
            DB::transaction(function () {
                $previous_status = $this->bid->status;
                $this->bidRepository->update($this->bid, ['status' => $this->status]);
                if ($this->status == config('b2b.BID_STATUSES')['accepted']) $this->procurementUpdater->setProcurement($this->bid->procurement)
                    ->setStatus(config('b2b.PROCUREMENT_STATUS')['accepted'])->updateStatus();
                $this->statusLogCreator->setBid($this->bid)->setPreviousStatus($previous_status)->setStatus($this->status)->create();
            });
        } catch (QueryException $e) {
            throw  $e;
        }
    }

    private function updateBidPrice()
    {
        $bid_price_quotation_item = $this->bid->items()->where('type', 'price_quotation')->first();
        if ($bid_price_quotation_item) {
            $this->bidRepository->update($this->bid, ['price' => (double)$bid_price_quotation_item->fields->sum('result')]);
        } else {
            $this->bidRepository->update($this->bid, ['price' => (double)$this->price]);
        }
    }

    private function sendHiringRequestNotification()
    {
        $message = $this->bid->procurement->owner->name . ' sent you a hiring request for BID #' . $this->bid->id;
        $link = config('sheba.partners_url') . '/' . $this->bid->bidder->sub_domain . '/procurements/' . $this->bid->procurement->id . '/summary';
        notify()->partner($this->bid->bidder)->send([
            'title' => $message,
            'type' => 'warning',
            'event_type' => get_class($this->bid),
            'event_id' => $this->bid->id,
            'link' => $link
        ]);
        event(new NotificationCreated([
            'notifiable_id' => $this->bid->bidder->id,
            'notifiable_type' => "partner",
            'event_id' => $this->bid->id,
            'event_type' => "bid",
            "title" => $message,
            "message" => $message,
            'link' => $link
        ], $this->bid->procurement->owner->id, get_class($this->bid->procurement->owner)));
    }

    private function notify($message)
    {
        foreach ($this->bid->procurement->owner->superAdmins as $member) {
            notify()->member($member)->send([
                'title' => $message,
                'type' => 'warning',
                'event_type' => get_class($this->bid),
                'event_id' => $this->bid->id
            ]);
            event(new NotificationCreated([
                'notifiable_id' => $member->id,
                'notifiable_type' => "member",
                'event_id' => $this->bid->id,
                'event_type' => "bid",
                "title" => $message,
                'message' => $message,
            ], $this->bid->bidder->id, get_class($this->bid->bidder)));
        }
    }
}