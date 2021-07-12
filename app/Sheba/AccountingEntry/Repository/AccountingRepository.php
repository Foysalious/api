<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Models\Partner;
use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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
        $this->setModifier($partner);
        $data = $this->createEntryData($request, $type, $request->source_id);
        $url = "api/entries/";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
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
        $this->setModifier($partner);
        $data = $this->createEntryData($request, $type, $request->source_id);
        $url = "api/entries/" . $entry_id;
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
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
        $this->setModifier($partner);
        $data = $this->createEntryData($request, $sourceType, $sourceId);
        $url = "api/entries/source/" . $sourceType . '/' . $sourceId;
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
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
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->delete($url);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
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
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->get($url);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $services
     * @param $requestedService
     * @return false|string
     */
    public function getInventoryProducts($services, $requestedService)
    {
        $requested_service = json_decode($requestedService, true);
        $inventory_products = [];
        foreach ($services as $key => $service) {
            $original_service = ($service->service);
            if ($original_service) {
                $sellingPrice = isset($requested_service[$key]['updated_price']) && $requested_service[$key]['updated_price'] ? $requested_service[$key]['updated_price'] : $original_service->price;
                $unitPrice = $original_service->cost ?: $sellingPrice;
                $inventory_products[] = [
                    "id" => $original_service->id ?? $requested_service[$key]['id'],
                    "name" => $original_service->name ?? $requested_service[$key]['name'],
                    "unit_price" => (double)$unitPrice,
                    "selling_price" => (double)$sellingPrice,
                    "quantity" => isset($requested_service[$key]['quantity']) ? $requested_service[$key]['quantity'] : 1
                ];
            } else {
                $sellingPrice = isset($requested_service[$key]['updated_price']) ? $requested_service[$key]['updated_price'] : $original_service->price;
                $inventory_products[] = [
                    "id" => 0,
                    "name" => 'Custom Amount',
                    "unit_price" => $sellingPrice,
                    "selling_price" => isset($original_service->cost) ? $original_service->cost : $sellingPrice,
                    "quantity" => isset($requested_service[$key]['quantity']) ? $requested_service[$key]['quantity'] : 1
                ];
            }
        }
        if (count($inventory_products) > 0) {
            return json_encode($inventory_products);
        }

        return null;
    }

    /**
     * @param $request
     * @param $type
     * @param null $type_id
     * @return array
     */
    private function createEntryData($request, $type, $type_id = null): array
    {
        $data['created_from']               = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount']                     = (double)$request->amount;
        $data['source_type']                = $type;
        $data['source_id']                  = $type_id;
        $data['note']                       = isset($request->note) ? $request->note : null;
        $data['amount_cleared']             = $request->amount_cleared;
        $data['debit_account_key']          = (string)$request->to_account_key; // to = debit = je account e jabe
        $data['credit_account_key']         = (string)$request->from_account_key; // from = credit = je account theke jabe
        $data['customer_id']                = isset($request->customer_id) ? $request->customer_id : null;
        $data['customer_name']              = isset($request->customer_id) ? $request->customer_name : null;
        $data['inventory_products']         = isset($request->inventory_products) ? $request->inventory_products : null;
        $data['entry_at']                   = isset($request->date) ? $request->date : Carbon::now()->format('Y-m-d H:i:s');
        $data['attachments']                = $this->uploadAttachments($request);
        $data['total_discount']             = isset($request->total_discount) ? (double)$request->total_discount : null;
        $data['total_vat']                  = isset($request->total_vat) ? (double)$request->total_vat : null;
        $data['bank_transaction_charge']    = isset($request->bank_transaction_charge) ? $request->bank_transaction_charge : null;
        $data['interest']                   = isset($request->interest) ? $request->interest : null;
        $data['details']                    = isset($request->details) ? $request->details : null;
        $data['reference']                  = isset($request->reference) ? $request->reference : null;
        return $data;
    }
}
