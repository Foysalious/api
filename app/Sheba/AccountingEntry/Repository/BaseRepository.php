<?php namespace App\Sheba\AccountingEntry\Repository;


use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Repositories\FileRepository;
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
    public function getCustomer($request)
    {
        $partner = $this->getPartner($request);
        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if ( $request->has('customer_id') && empty($partner_pos_customer)){
            $customer = PosCustomer::find($request->customer_id);
            if(!$customer) throw new AccountingEntryServerError('pos customer not available', 404);
            $partner_pos_customer = PartnerPosCustomer::create(['partner_id' => $partner->id, 'customer_id' => $request->customer_id]);
        }
        if ($partner_pos_customer) {
            $request->customer_id = $partner_pos_customer->customer_id;
            $request->customer_name = $partner_pos_customer->details()["name"];
        }
        return $request;
    }

    public function uploadAttachments($request)
    {
        $attachments = [];
        if ($request->has("attachments") && $request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $key => $file) {
                if (!empty($file)) {
                    list($file, $filename) = $this->makeAttachment($file, '_' . getFileName($file) . '_attachments');
                    $attachments[] = $this->saveFileToCDN($file, getDueTrackerAttachmentsFolder(), $filename);
                }
            }
        }

//        $old_attachments = $request->old_attachments ?: [];
//        if ($request->has('attachment_should_remove') && (!empty($request->attachment_should_remove))) {
//            $this->deleteFromCDN($request->attachment_should_remove);
//            $old_attachments = array_diff($old_attachments, $request->attachment_should_remove);
//        }
//
//        $attachments = array_filter(array_merge($attachments, $old_attachments));
        return json_encode($attachments);
    }

    /**
     * @param $files
     */
    private function deleteFromCDN($files)
    {
        foreach ($files as $file) {
            $filename = substr($file, strlen(env('S3_URL')));
            (new FileRepository())->deleteFileFromCDN($filename);
        }
    }

    private function getPartner($request)
    {
        if(isset($request->partner->id)) {
            $partner_id = $request->partner->id;
        } else {
            $partner_id = (int) $request->partner;
        }
        return Partner::find($partner_id);
    }
}