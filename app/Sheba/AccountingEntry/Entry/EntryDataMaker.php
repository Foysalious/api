<?php

namespace App\Sheba\AccountingEntry\Entry;


use App\Sheba\AccountingEntry\Service\DueTrackerContactResolver;
use Carbon\Carbon;
use Exception;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class EntryDataMaker
{
    use ModificationFields;
    protected $partner;
    protected $entryDto;

    /**
     * @param mixed $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $entryDto
     */
    public function setEntryDto($entryDto)
    {
        $this->entryDto = $entryDto;
        return $this;
    }


    /**
     * @throws Exception
     */
    public function makeData(): array
    {
        $data['created_from'] = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount'] = (double)$this->entryDto->amount;
        $data['source_type'] = $this->entryDto->source_type;
        $data['debit_account_key'] = $this->entryDto->getToAccountKey(); // to = debit = je account e jabe
        $data['credit_account_key'] = $this->entryDto->getFromAccountKey(); // from = credit = je account theke jabe
        $data['note'] = $this->entryDto->note ?? null;
        $data['amount_cleared'] = $this->entryDto->amount_cleared ?? 0;
        $data['reconcile_amount'] = $this->entryDto->reconcile_amount ?? 0;
        $data['updated_entry_amount'] = $this->entryDto->updated_entry_amount ?? 0;
        $data['entry_at'] = $this->entryDto->entry_at ?? Carbon::now()->format('Y-m-d H:i:s');
        $data['total_vat'] = isset($this->entryDto->total_vat) ? (double)$this->entryDto->total_vat : 0;
        $data['delivery_charge'] = isset($this->entryDto->delivery_charge) ? (double)$this->entryDto->delivery_charge : 0;
        $data['total_discount'] = isset($this->entryDto->total_discount) ? (double)$this->entryDto->total_discount : 0;
        $data['interest'] = $this->entryDto->interest ?? 0;
        $data['details'] = $this->entryDto->details ?? null;
        $data['bank_transaction_charge'] = $this->entryDto->bank_transaction_charge ?? 0;
        $data['paid_by'] = $this->entryDto->paid_by ?? null;
        $data['reference'] = $this->entryDto->reference ?? null;
        $data['is_due_tracker_payment_link'] = $this->entryDto->is_due_tracker_payment_link ?? null;
        $data['real_amount'] = $this->entryDto->real_amount ?? null;
        $data['contact_id'] = $this->entryDto->contact_id ?? null;
        $data['contact_type'] = $this->entryDto->contact_type ?? null;
        $data['attachments'] = $this->entryDto->attachments;
        if ($this->entryDto->contact_id && $this->entryDto->contact_type) {
            $data = array_merge($data, $this->getContactDetails($this->entryDto->contact_id, $this->entryDto->contact_type));
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