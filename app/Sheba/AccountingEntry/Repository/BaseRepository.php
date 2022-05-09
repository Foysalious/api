<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Exceptions\Pos\Customer\PosCustomerNotFoundException;
use App\Models\Partner;
use Exception;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\AccountingEntry\Repository\UserMigrationRepository;
use Sheba\Dal\UserMigration\UserStatus;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Pos\Customer\PosCustomerResolver;
use Sheba\Pos\Repositories\PosOrderPaymentRepository;

class BaseRepository
{
    use ModificationFields, CdnFileManager, FileManager;

    /** @var AccountingEntryClient $client */
    protected $client;

    CONST NOT_ELIGIBLE = 'not_eligible';

    /**
     * BaseRepository constructor.
     * @param AccountingEntryClient $client
     */
    public function __construct(AccountingEntryClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param $request
     * @return mixed
     * @throws Exception
     */
    public function getCustomer($request)
    {
        /* Todo need to resolve contact for customer and supplier individually */

        if (!isset($request->customer_id) || $request->customer_id == null) {
            return $request;
        }
        if (isset($request->customer_name) && isset($request->customer_mobile)) {
            return $request;
        }
        $partner = $this->getPartner($request);
        /** @var PosCustomerResolver $posCustomerResolver */
        $posCustomerResolver = app(PosCustomerResolver::class);
        $partner_pos_customer = $posCustomerResolver->setCustomerId($request->customer_id)->setPartner($partner)->get();
        if (!$partner_pos_customer) {
            throw new PosCustomerNotFoundException('Sorry! customer not found', 404);
        }
        $request->customer_id = $partner_pos_customer->id;
        $request->customer_name = $partner_pos_customer->name;
        $request->customer_mobile = $partner_pos_customer->mobile;
        $request->customer_pro_pic = $partner_pos_customer->pro_pic;
        $request->customer_is_supplier = $partner_pos_customer->is_supplier;
        return $request;
    }

    public function uploadAttachments($request)
    {
        $attachments = $this->uploadFiles($request);
        return json_encode($attachments);
    }


    private function uploadFiles($request): array
    {
        $attachments=[];
        if (isset($request->attachments) && !empty($request->attachments) && request()->hasFile('attachments')) {
            foreach (request()->file('attachments') as $key => $file) {
                if (!empty($file)) {
                    list($file, $filename) = $this->makeAttachment($file, '_' . getFileName($file) . '_attachments');
                    $attachments[] = $this->saveFileToCDN($file, getDueTrackerAttachmentsFolder(), $filename);
                }
            }
        }
        return $attachments;
    }

    protected function updateAttachments($request){
        $attachments=$this->uploadFiles($request);
        $old_attachments = $request->old_attachments ?: [];
        if ($request->has('attachment_should_remove') && (!empty($request->attachment_should_remove))) {
            foreach ($request->attachment_should_remove as $item){
                $this->deleteFile($item);
            }
            $old_attachments = array_diff($old_attachments, $request->attachment_should_remove);
        }

        $attachments = array_filter(array_merge($attachments, $old_attachments));
        return json_encode($attachments);
    }

    protected function getPartner($request)
    {
        $partner_id = $request->partner->id ?? (int)$request->partner;
        return Partner::find($partner_id);
    }

    /**
     * @param $userId
     * @return bool
     */
    public function isMigratedToAccounting($userId): bool
    {
        return true;
//        $arr = [self::NOT_ELIGIBLE, UserStatus::PENDING, UserStatus::UPGRADING, UserStatus::FAILED];
//        /** @var UserMigrationRepository $userMigrationRepo */
//        $userMigrationRepo = app(UserMigrationRepository::class);
//        $userStatus = $userMigrationRepo->userStatus($userId);
//        if (in_array($userStatus, $arr)) return false;
//        return true;
    }
}