<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\RequestIdentification;

class DueTrackerRepository extends BaseRepository
{
    private $entry_id, $partner;

    /**
     * @param $entry_id
     * @return $this
     */
    public function setEntryId($entry_id)
    {
        $this->entry_id = $entry_id;
        return $this;
    }

    /**
     * @param $partner
     * @return $this
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function storeEntry(Request $request, $type, $with_update = false) {
        $this->getCustomer($request);
        $this->setModifier($request->partner);
        $data     = $this->createEntryData($request, $type);
        $url = $with_update ? "api/entries/".$request->entry_id : "api/entries/";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    private function createEntryData(Request $request, $type)
    {
        $data['created_from']       = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount']             = (double)$request->amount;
        $data['source_type']        = $type;
        $data['note']               = $request->note;
        $data['debit_account_key']  = $type === EntryTypes::DUE ? $request->customer_id : $request->account_key;
        $data['credit_account_key'] = $type === EntryTypes::DUE ? $request->account_key : $request->customer_id;
        $data['customer_id']        = $request->customer_id;
        $data['customer_name']      = $request->customer_name;
        $data['entry_at']           = $request->date ?: Carbon::now()->format('Y-m-d H:i:s');
        $data['attachments']        = $this->uploadAttachments($request);
        return $data;
    }

    /**
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function deleteEntry()
    {
        try {
            $url = "api/entries/".$this->entry_id;
            return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->delete($url);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function entryDetails() {
        try {
            $url = "api/entries/".$this->entry_id;
            return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function getDueList($request) {
        try {
            $url = "api/due-list?";
            $url      = $this->updateRequestParam($request, $url);
            return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    private function updateRequestParam(Request $request, $url)
    {
        $order_by = $request->order_by;
        if (!empty($order_by) && $order_by != "name") {
            $order = !empty($request->order) ? strtolower($request->order) : 'desc';
            $url   .= "&order_by=$order_by&order=$order";
        }

        if($request->has('balance_type')) {
            $url   .= "&balance_type=$request->balance_type";
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $url .= "&start=$request->start_date&end=$request->end_date";
        }
        return $url;
    }
}