<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Statics\IncomeExpenseStatics;
use Sheba\RequestIdentification;

class AccountingRepository extends BaseRepository
{
    public function accountTransfer(Request $request){
        $source_id = rand(0000,9999).date('s').preg_replace("/^.*\./i","", microtime(true));
        $this->setModifier($request->partner);
        $data     = $this->createJournalData($request, EntryTypes::TRANSFER, $source_id);
        $url = "api/journals/";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            logError($e);
        }
    }

    /**
     * @param Request $request
     * @param $type
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function storeEntry(Request $request, $type) {
        $this->getCustomer($request);
        $this->setModifier($request->partner);
        $data     = $this->createEntryData($request, $type, $request->source_id);
        $url = "api/entries/";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }


    public function getAccountsTotal(Request $request) {
        $data = IncomeExpenseStatics::createDataForAccountsTotal($request->account_type, $request->start_date, $request->end_date);
        $url  = "api/reports/account-list-with-sum";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->get($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function updateEntryBySource(Request $request, $sourceType, $sourceId)
    {
        $this->getCustomer($request);
        $this->setModifier($request->partner);
        $data     = $this->createEntryData($request, $sourceType, $sourceId);
        $url = "api/entries/source/".$sourceType.'/'.$sourceId;
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }
    /**
     * @param $request
     * @param $type
     * @param null $type_id
     * @return array
     */
    private function createEntryData($request, $type, $type_id = null): array
    {
        $data['created_from']       = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
        $data['amount']             = (double)$request->amount;
        $data['source_type']        = $type;
        $data['source_id']          = $type_id;
        $data['note']               = $request->note;
        $data['amount_cleared']     = $request->amount_cleared;
        $data['debit_account_key']  = $request->from_account_key;
        $data['credit_account_key'] = $request->to_account_key;
        $data['customer_id']        = $request->customer_id;
        $data['customer_name']      = $request->customer_name;
        $data['inventory_products'] = $request->inventory_products;
        $data['entry_at']           = $request->date ?: Carbon::now()->format('Y-m-d H:i:s');
        $data['attachments']        = $this->uploadAttachments($request);
        $data['total_discount']     = (double)$request->total_discount;
        return $data;
    }

    private function createJournalData(Request $request, $source_type, $source_id)
    {
            $data['amount']             = (double)$request->amount;
            $data['source_type']        = $source_type;
            $data['source_id']          = $source_id;
            $data['debit_account_key']  = $request->from_account_key;
            $data['credit_account_key'] = $request->to_account_key;
            $data['entry_at']           = $request->date;
            $data['details']            = $request->note;
            $data['created_from']       = json_encode($this->withBothModificationFields((new RequestIdentification())->get()));
            return $data;
    }
}