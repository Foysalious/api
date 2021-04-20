<?php namespace App\Sheba\AccountingEntry\Repository;




use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\RequestIdentification;

class DueTrackerRepository extends BaseRepository
{
    public function storeEntry(Request $request, $type) {
        $this->getCustomer($request);
        $this->setModifier($request->partner);
        $data     = $this->createEntryData($request, $type);
        $url = "api/entries/";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            logError($e);
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
}