<?php

namespace App\Sheba\AccountingEntry\Entry;

use App\Sheba\AccountingEntry\Dto\EntryDTO;
use App\Sheba\AccountingEntry\Helper\FileUploader;
use App\Sheba\AccountingEntry\Service\DueTrackerContactResolver;
use Carbon\Carbon;
use Exception;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class EntryDataMaker
{
    use FileUploader, ModificationFields;
    protected $partner;

    /**
     * @param mixed $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function makeData(EntryDTO $entry_dto): array
    {
        $data['created_from'] = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount'] = (double)$entry_dto->amount;
        $data['source_type'] = $entry_dto->source_type;
        $data['debit_account_key'] = $entry_dto->getToAccountKey(); // to = debit = je account e jabe
        $data['credit_account_key'] = $entry_dto->getFromAccountKey(); // from = credit = je account theke jabe
        $data['note'] = $entry_dto->note ?? null;
        $data['amount_cleared'] = $entry_dto->amount_cleared ?? 0;
        $data['reconcile_amount'] = $entry_dto->reconcile_amount ?? 0;
        $data['updated_entry_amount'] = $entry_dto->updated_entry_amount ?? 0;
        $data['entry_at'] = $entry_dto->entry_at ?? Carbon::now()->format('Y-m-d H:i:s');
        $data['attachments'] = isset($entry_dto->attachments) ?
            $this->uploadAttachments($entry_dto->attachments) : json_encode([]);
        $data['total_vat'] = isset($entry_dto->total_vat) ? (double)$entry_dto->total_vat : 0;
        $data['delivery_charge'] = isset($entry_dto->delivery_charge) ? (double)$entry_dto->delivery_charge : 0;
        $data['total_discount'] = isset($entry_dto->total_discount) ? (double)$entry_dto->total_discount : 0;
        $data['interest'] = $entry_dto->interest ?? 0;
        $data['details'] = $entry_dto->details ?? null;
        $data['bank_transaction_charge'] = $entry_dto->bank_transaction_charge ?? 0;
        $data['paid_by'] = $entry_dto->paid_by ?? null;
        $data['reference'] = $entry_dto->reference ?? null;
        $data['is_due_tracker_payment_link'] = $entry_dto->is_due_tracker_payment_link ?? null;
        $data['real_amount'] = $entry_dto->real_amount ?? null;
        $data['contact_id'] = $entry_dto->contact_id ?? null;
        $data['contact_type'] = $entry_dto->contact_type ?? null;

        if ($entry_dto->contact_id && $entry_dto->contact_type) {
            $data = array_merge($data, $this->getContactDetails($entry_dto->contact_id, $entry_dto->contact_type));
        }
        return $data;
    }

    /**
     * @throws Exception
     */
    private function getContactDetails(string $contact_id, string $contact_type): array
    {
        /** @var DueTrackerContactResolver $contact_resolver */
        $contact_resolver = app()->make(DueTrackerContactResolver::class);
        return $contact_resolver->setContactType($contact_type)
            ->setContactId($contact_id)
            ->setPartner($this->partner)
            ->getContactDetails();
    }
}