<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class AccountingRepository extends BaseRepository
{
    use ModificationFields, CdnFileManager, FileManager;

    public function accountTransfer(Request $request){
        $source_id = rand(0000,9999).date('s').preg_replace("/^.*\./i","", microtime(true));
        $this->setModifier($request->partner);
        $data     = $this->createJournalData($request, "transfer", $source_id);
        $url = "api/journals";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            logError($e);
        }
    }

    public function storeEntry(Request $request, $type) {
        $this->getCustomer($request);
        $this->setModifier($request->partner);
        $data     = $this->createEntryData($request, $type);
        $url = "api/entries";
        try {
            return $this->client->setUserType(UserType::PARTNER)->setUserId($request->partner->id)->post($url, $data);
        } catch (AccountingEntryServerError $e) {
            logError($e);
        }
    }

    /**
     * @param Request $request
     * @param $type
     * @param null $type_id
     * @return array
     */
    private function createEntryData(Request $request, $type, $type_id = null)
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
        $data['entry_at']           = $request->date ?: Carbon::now()->format('Y-m-d H:i:s');
        $data['attachments']        = $this->uploadAttachments($request);
        return $data;
    }

    private function uploadAttachments(Request $request)
    {
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $key => $file) {
                if (!empty($file)) {
                    list($file, $filename) = $this->makeAttachment($file, '_' . getFileName($file) . '_attachments');
                    $attachments[] = $this->saveFileToCDN($file, getDueTrackerAttachmentsFolder(), $filename);
                }
            }
        }
        return json_encode($attachments);
    }

    private function getCustomer(Request $request)
    {
        $partner_pos_customer = PartnerPosCustomer::byPartner($request->partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if ( $request->has('customer_id') && empty($partner_pos_customer))
            $partner_pos_customer = PartnerPosCustomer::create(['partner_id' => $request->partner->id, 'customer_id' => $request->customer_id]);

        if ($partner_pos_customer) {
            $request['customer_id'] = $partner_pos_customer->id;
            $request['customer_name'] = $partner_pos_customer->details()["name"];
        }

        return $request;
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