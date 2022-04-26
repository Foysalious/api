<?php

namespace App\Sheba\AccountingEntry\Service;

use App\Models\Partner;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\EntriesRepository;
use App\Sheba\Pos\Order\PosOrderObject;
use Carbon\Carbon;
use Sheba\Pos\Order\PosOrderResolver;
use Exception;

class EntriesService
{
    /**
     * @var EntriesRepository
     */
    private $entryRepo;

    public function __construct(EntriesRepository $entryRepo)
    {
        $this->entryRepo = $entryRepo;
    }

    //NOTICE: THIS METHOD ONLY SUPPORTING EntriesControllerV2@details
    public function entryDetails(Partner $partner, int $entryId)
    {
        $data = $this->entryRepo->setPartner($partner)->setEntryId($entryId)->entryDetails();
        $data['created_at'] = Carbon::parse($data['created_at'])->format('Y-m-d h:i:s');
        $data['updated_at'] = Carbon::parse($data['updated_at'])->format('Y-m-d h:i:s');
        $data['entry_at'] = Carbon::parse($data['entry_at'])->format('Y-m-d h:i:s');
        unset($data["customer_details"]);
        if ($data["extra_payload"]) {
            $data["extra_payload"] = json_decode($data["extra_payload"]);
        }
        if ($data['source_type'] == EntryTypes::POS) {
            if (isset($data["extra_payload"]["partner_wise_order_id"])) {
                $data["partner_wise_order_id"] = $data["extra_payload"]["partner_wise_order_id"];
            } else {
                $posOrder = $this->posOrderByOrderId($data['source_id']);
                if ($posOrder) {
                    $data["partner_wise_order_id"] = $posOrder->partner_wise_order_id;
                }
            }
        }
        return $data;
    }

    //NOTICE: THIS METHOD ONLY SUPPORTING EntriesControllerV2@deleteEntry
    public function deleteEntry(Partner $partner, int $entryId)
    {
        return $this->entryRepo->setPartner($partner)->setEntryId($entryId)->deleteEntry();
    }

    private function posOrderByOrderId(int $orderId): ?PosOrderObject
    {
        try {
            /** @var PosOrderResolver $posOrderResolver */
            $posOrderResolver = app(PosOrderResolver::class);
            return $posOrderResolver->setOrderId($orderId)->get();
        } catch (Exception $e) {
            return null;
        }
    }

}