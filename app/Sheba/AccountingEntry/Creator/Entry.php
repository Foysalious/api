<?php

namespace App\Sheba\AccountingEntry\Creator;


use App\Sheba\AccountingEntry\Dto\EntryDTO;
use App\Sheba\AccountingEntry\Helper\FileUploader;
use App\Sheba\AccountingEntry\Repository\EntriesRepository;
use Carbon\Carbon;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class Entry
{
    use FileUploader;

    use ModificationFields;
    protected $entriesRepo;
    /* @var EntryDTO $entryDto */
    protected $entryDto;
    protected $partner;

    public function __construct(EntriesRepository $entries_repo)
    {
        $this->entriesRepo = $entries_repo;
    }

    /**
     * @param mixed $entryDto
     */
    public function setEntryDto(EntryDTO $entryDto)
    {
        $this->entryDto = $entryDto;
        return $this;
    }

    /**
     * @param mixed $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function createEntry()
    {
        $data = $this->makeEntryData();
        return $this->entriesRepo->setPartner($this->partner)->createEntry($data);

    }

    private function makeEntryData()
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
        $data['attachments'] = isset($this->entryDto->attachments) ?
            $this->uploadAttachments($this->entryDto->attachments) : null;
        $data['total_discount'] = isset($this->entryDto->total_discount) ? (double)$this->entryDto->total_discount : 0;
        $data['total_vat'] = isset($this->entryDto->total_vat) ? (double)$this->entryDto->total_vat : 0;
        $data['delivery_charge'] = isset($this->entryDto->delivery_charge) ? (double)$this->entryDto->delivery_charge : 0;
        $data['bank_transaction_charge'] = $this->entryDto->bank_transaction_charge ?? 0;
        $data['interest'] = $this->entryDto->interest ?? 0;
        $data['details'] = $this->entryDto->details ?? null;
        $data['reference'] = $this->entryDto->reference ?? null;
        $data['paid_by'] = $this->entryDto->paid_by ?? null;
        $data['is_due_tracker_payment_link'] = $this->entryDto->is_due_tracker_payment_link ?? null;
        $data['real_amount'] = $this->entryDto->real_amount ?? null;
        $data['contact_id'] = $this->entryDto->contact_id ?? null;
        $data['contact_type'] = $this->entryDto->contact_type ?? null;
        return array_merge($data, $this->getContactDetails());

    }

    private function getContactDetails()
    {
        return [
            'contact_id' => $this->entryDto->contact_id,
            'contact_name' => $this->entryDto->contact_name,
            'contact_mobile' => $this->entryDto->contact_mobile,
            'contact_pro_pic' => $this->entryDto->contact_pro_pic,
        ];
    }

}