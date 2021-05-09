<?php namespace App\Sheba\AccountingEntry\Repository;


use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;

class BaseRepository
{
    use ModificationFields, CdnFileManager, FileManager;

    /** @var AccountingEntryClient $client */
    protected $client;

    /**
     * BaseRepository constructor.
     * @param AccountingEntryClient $client
     */
    public function __construct(AccountingEntryClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getCustomer(Request $request)
    {
        $partner_pos_customer = PartnerPosCustomer::byPartner($request->partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if ( $request->has('customer_id') && empty($partner_pos_customer)){
            $customer = PosCustomer::find($request->customer_id);
            if(!$customer) throw new AccountingEntryServerError('pos customer not available', 404);
            $partner_pos_customer = PartnerPosCustomer::create(['partner_id' => $request->partner->id, 'customer_id' => $request->customer_id]);
        }

        if ($partner_pos_customer) {
            $request['customer_id'] = $partner_pos_customer->customer_id;
            $request['customer_name'] = $partner_pos_customer->details()["name"];
        }
        return $request;
    }

    public function uploadAttachments(Request $request)
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
}