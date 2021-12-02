<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Models\Partner;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Statics\IncomeExpenseStatics;
use Sheba\RequestIdentification;

class AccountingRepository extends BaseRepository
{
    /**
     * @param $request
     * @param $type
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function storeEntry($request, $type)
    {
        $this->getCustomer($request);
        $partner = $this->getPartner($request);
        if (!$this->isMigratedToAccounting($partner->id)) {
            return true;
        }
        $this->setModifier($partner);
        $data = $this->createEntryData($request, $type, $request->source_id);
        $url = "api/entries/";
        $data = $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->post($url, $data);
        foreach ($data as $datum) {
            //pos order reconcile while storing entry
            if ($type !== EntryTypes::POS && $datum['source_type'] == 'pos' && $datum['amount_cleared'] > 0) {
                $this->createPosOrderPayment($datum['amount_cleared'], $datum['source_id'], 'cod');
            }
        }

        return $data;
    }

    /**
     * @param $request
     * @param $type
     * @param $entry_id
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function updateEntry($request, $type, $entry_id)
    {
        $this->getCustomer($request);
        $partner = $this->getPartner($request);
        if (!$this->isMigratedToAccounting($partner->id)) {
            return true;
        }
        $this->setModifier($partner);
        $data = $this->createEntryData($request, $type, $request->source_id);
        $url = "api/entries/" . $entry_id;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->post($url, $data);
    }

    /**
     * @param $request
     * @param $sourceId
     * @param $sourceType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function updateEntryBySource($request, $sourceId, $sourceType)
    {
        $this->getCustomer($request);
        $partner = $this->getPartner($request);
        if (!$this->isMigratedToAccounting($partner->id)) {
            return true;
        }
        $this->setModifier($partner);
        $data = $this->createEntryData($request, $sourceType, $sourceId);
        $url = "api/entries/source/" . $sourceType . '/' . $sourceId;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->post($url, $data);
    }

    /**
     * @param Partner $partner
     * @param $sourceType
     * @param $sourceId
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function deleteEntryBySource(Partner $partner, $sourceType, $sourceId)
    {
        $url = "api/entries/source/" . $sourceType . '/' . $sourceId;
        if (!$this->isMigratedToAccounting($partner->id)) {
            return true;
        }
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->delete($url);
    }

    /**
     * @param $request
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function getAccountsTotal($request)
    {
        list($start, $end) = IncomeExpenseStatics::createDataForAccountsTotal($request->start_date, $request->end_date);
        $url = "api/reports/account-list-with-sum/{$request->account_type}?start_date=$start&end_date=$end";
        return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->get($url);
    }

    /**
     * @param $request
     * @param $type
     * @param null $type_id
     * @return array
     */
    private function createEntryData($request, $type, $type_id = null): array
    {
        $data['created_from'] = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount'] = (double)$request->amount;
        $data['source_type'] = $type;
        $data['source_id'] = $type_id;
        $data['debit_account_key'] = $request->to_account_key; // to = debit = je account e jabe
        $data['credit_account_key'] = $request->from_account_key; // from = credit = je account theke jabe
        $data['note'] = $request->note ?? null;
        $data['amount_cleared'] = $request->amount_cleared ?? 0;
        $data['reconcile_amount'] = $request->reconcile_amount ?? 0;
        $data['updated_entry_amount'] = $request->updated_entry_amount ?? 0;
        $data['customer_id'] = $request->customer_id ?? null;
        $data['customer_name'] = $request->customer_name ?? null;
        $data['customer_mobile'] = $request->customer_mobile ?? null;
        $data['customer_pro_pic'] = $request->customer_pro_pic ?? null;
        $data['customer_is_supplier'] = $request->customer_is_supplier ?? null;
        $data['inventory_products'] = $request->inventory_products ?? null;
        $data['entry_at'] = $request->date ?? Carbon::now()->format('Y-m-d H:i:s');
        $data['attachments'] = $this->uploadAttachments($request);
        $data['total_discount'] = isset($request->total_discount) ? (double)$request->total_discount : 0;
        $data['total_vat'] = isset($request->total_vat) ? (double)$request->total_vat : 0;
        $data['delivery_charge'] = isset($request->delivery_charge) ? (double)$request->delivery_charge : 0;
        $data['bank_transaction_charge'] = $request->bank_transaction_charge ?? 0;
        $data['interest'] = $request->interest ?? 0;
        $data['details'] = $request->details ?? null;
        $data['reference'] = $request->reference ?? null;
        $data['paid_by'] = $request->paid_by ?? null;
        $data['is_due_tracker_payment_link'] = $request->is_due_tracker_payment_link ?? null;
        return $data;
    }
}