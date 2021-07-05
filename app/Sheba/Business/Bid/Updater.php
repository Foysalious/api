<?php namespace App\Sheba\Business\Bid;

use App\Models\Bid;
use App\Models\BidItem;
use App\Models\BidItemField;
use App\Models\Partner;
use App\Sheba\Bitly\BitlyLinkShort;
use App\Sheba\Repositories\Business\BidRepository;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Exception;
use Illuminate\Database\QueryException;
use Sheba\Business\BidStatusChangeLog\Creator;
use Sheba\Notification\NotificationCreated;
use Sheba\Repositories\Interfaces\BidItemFieldRepositoryInterface;
use DB;
use App\Sheba\Business\Procurement\Updater as ProcurementUpdater;
use Sheba\Sms\Sms;

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
    /** @var BitlyLinkShort */
    private $bitlyLink;

    /**
     * Updater constructor.
     * @param BidRepository $bid_repository
     * @param BidItemFieldRepositoryInterface $bid_item_field_repository
     * @param Creator $creator
     * @param ProcurementUpdater $procurement_updater
     * @param BitlyLinkShort $bitly_link
     */
    public function __construct(BidRepository $bid_repository,
                                BidItemFieldRepositoryInterface $bid_item_field_repository,
                                Creator $creator, ProcurementUpdater $procurement_updater,
                                BitlyLinkShort $bitly_link)
    {
        $this->bidRepository = $bid_repository;
        $this->bidItemFieldRepository = $bid_item_field_repository;
        $this->statusLogCreator = $creator;
        $this->procurementUpdater = $procurement_updater;
        $this->bitlyLink = $bitly_link;
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
                $this->updatePartnerCommission();
                $this->statusLogCreator->setBid($this->bid)->setPreviousStatus($previous_status)->setStatus($this->status)->create();

                if ($this->status == 'sent') $this->sendVendorParticipatedNotification();
                elseif ($this->status == 'rejected') $this->sendBidRejectedNotification();
                elseif ($this->status == 'accepted') $this->sendBidAcceptedNotification();
            });
        } catch (QueryException $e) {
            throw  $e;
        }
    }

    private function sendVendorParticipatedNotification()
    {
        $link = config('sheba.business_url') . '/dashboard/rfq/list/' . $this->bid->procurement_id . '/biddings/' . $this->bid->id;
        $message = $this->bid->bidder->name . ' participated on your procurement #' . $this->bid->procurement->id;

        $this->notify($message, $link);
    }

    private function sendBidRejectedNotification()
    {
        $link = config('sheba.business_url') . '/dashboard/rfq/list/' . $this->bid->procurement_id . '/biddings/' . $this->bid->id;
        $message = $this->bid->bidder->name . ' rejected your hiring request #' . $this->bid->id;

        $this->notify($message, $link);
    }

    private function sendBidAcceptedNotification()
    {
        $link = config('sheba.business_url') . '/dashboard/rfq/orders/' . $this->bid->procurement_id . '/details?bidId=' . $this->bid->id;
        $message = $this->bid->bidder->name . ' accepted your hiring request #' . $this->bid->id;

        $this->notify($message, $link);
    }

    public function hire()
    {
        try {
            DB::transaction(function () {
                $previous_status = $this->bid->status;
                $this->bidRepository->update($this->bid, ['status' => 'awarded', 'terms' => $this->terms, 'policies' => $this->policies]);

                if ($this->bid->isAdvanced()) {
                    /** @var BidItem $bid_price_quotation_item */
                    $bid_price_quotation_item = $this->bid->items()->where('type', 'price_quotation')->first();
                    $price_quotation_item = $this->items->where('id', $bid_price_quotation_item->id)->first();
                    $fields = collect($price_quotation_item->fields);
                    foreach ($bid_price_quotation_item->fields as $field) {
                        /** @var BidItemField $field */
                        $field_result = $fields->where('id', $field->id)->first();
                        if ($field_result) {
                            $variables = null;
                            if ($field_result->unit) {
                                $variables = json_decode($field->variables);
                                $variables->unit = $field_result->unit;
                                $variables = json_encode($variables);
                            }

                            $this->bidItemFieldRepository->update($field, ['bidder_result' => $field->result]);
                            $this->bidItemFieldRepository->update($field, [
                                'result'    => isset($field_result->result) ? $field_result->result : $field->result,
                                'variables' => $variables ? $variables : $field->variables,
                                'title'     => isset($field_result->title) ? $field_result->title : $field->title,
                                'short_description' => isset($field_result->short_description) ? $field_result->short_description : $field->short_description,
                            ]);
                        }
                    }
                }

                $this->bidRepository->update($this->bid, ['bidder_price' => (double)$this->bid->price]);
                $this->updateBidPrice();
                $this->statusLogCreator->setBid($this->bid)->setPreviousStatus($previous_status)->setStatus($this->bid->status)->create();
                // $this->sendHiringRequestNotification();
                $this->smsForHiringRequest();
            });
        } catch (QueryException $e) {
            throw  $e;
        }
    }

    private function smsForHiringRequest()
    {
        /** @var Partner $partner */
        $partner = $this->bid->bidder;
        $message = $this->bid->procurement->owner->name . ' sent you a hiring request for BID #' . $this->bid->id;
        $procurement_id = $this->bid->procurement->id;
        $bid_id = $this->bid->id;
        $url = config('sheba.business_url') . "/tender/$procurement_id/hire/$bid_id";
        (new Sms())
            ->setFeatureType(FeatureType::BID)
            ->setBusinessType(BusinessType::B2B)
            ->to($partner->getManagerMobile())
            ->message("$message. Now go to this link-" . $this->bitlyLink->shortUrl($url))
            ->shoot();
    }

    public function updateStatus()
    {
        try {
            DB::transaction(function () {
                $previous_status = $this->bid->status;
                $this->bidRepository->update($this->bid, ['status' => $this->status]);
                if ($this->status == config('b2b.BID_STATUSES')['accepted']) {
                    $this->procurementUpdater
                        ->setProcurement($this->bid->procurement)
                        ->setStatus(config('b2b.PROCUREMENT_STATUS')['accepted'])
                        ->updateStatus();
                }
                $this->updatePartnerCommission();
                $this->statusLogCreator
                    ->setBid($this->bid)
                    ->setPreviousStatus($previous_status)
                    ->setStatus($this->status)
                    ->create();

                if ($this->status == 'rejected') $this->sendBidRejectedNotification();
                elseif ($this->status == 'accepted') $this->sendBidAcceptedNotification();
            });
        } catch (QueryException $e) {
            throw  $e;
        }
    }

    private function updateBidPrice()
    {
        $bid_price_quotation_item = $this->bid->items()->where('type', 'price_quotation')->first();
        $price = ($bid_price_quotation_item) ? $bid_price_quotation_item->fields->sum('result') : $this->price;
        $this->bidRepository->update($this->bid, ['price' => (double)$price]);
    }

    private function updatePartnerCommission()
    {
        $this->bidRepository->update($this->bid, ['commission_percentage' => (double)$this->bid->bidder->commission]);
    }

    private function sendHiringRequestNotification()
    {
        try {
            $message = $this->bid->procurement->owner->name . ' sent you a hiring request for BID #' . $this->bid->id;
            $link = config('sheba.partners_url') . '/' . $this->bid->bidder->sub_domain . '/procurements/' . $this->bid->procurement->id . '/summary';
            notify()->partner($this->bid->bidder)->send([
                'title' => $message,
                'type' => 'warning',
                'event_type' => get_class($this->bid),
                'event_id' => $this->bid->id,
                'link' => $link
            ]);
        } catch (Exception $e) {}
    }

    private function notify($message, $link)
    {
        foreach ($this->bid->procurement->owner->superAdmins as $member) {
            notify()->member($member)->send([
                'title' => $message,
                'type' => 'warning',
                'event_type' => get_class($this->bid),
                'event_id' => $this->bid->id,
                'link' => $link
            ]);
        }
    }
}
