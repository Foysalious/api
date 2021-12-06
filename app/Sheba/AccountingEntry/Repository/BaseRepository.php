<?php namespace App\Sheba\AccountingEntry\Repository;

use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\AccountingEntry\Repository\UserMigrationRepository;
use Sheba\Dal\UserMigration\UserStatus;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Pos\Payment\Creator as PaymentCreator;

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
     * @throws AccountingEntryServerError
     */
    public function getCustomer($request)
    {
        $partner = $this->getPartner($request);
        $partner_pos_customer = PartnerPosCustomer::byPartner($partner->id)->where('customer_id', $request->customer_id)->with(['customer'])->first();
        if ( isset($request->customer_id) && empty($partner_pos_customer)){
            $customer = PosCustomer::find($request->customer_id);
            if(!$customer) throw new AccountingEntryServerError('pos customer not available', 404);
            $partner_pos_customer = PartnerPosCustomer::create(['partner_id' => $partner->id, 'customer_id' => $request->customer_id]);
        }
        if ($partner_pos_customer) {
            $request->customer_id = $partner_pos_customer->customer_id;
            $request->customer_name = $partner_pos_customer->details()["name"];
            $request->customer_mobile = $partner_pos_customer->details()["phone"];
            $request->customer_pro_pic = $partner_pos_customer->details()["image"];
            $request->customer_is_supplier = $partner_pos_customer->is_supplier;
        }
        return $request;
    }

    public function uploadAttachments($request)
    {
        $attachments = [];
//        todo: have to refactor the attachment
        if (isset($request->attachments) && $request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $key => $file) {
                if (!empty($file)) {
                    list($file, $filename) = $this->makeAttachment($file, '_' . getFileName($file) . '_attachments');
                    $attachments[] = $this->saveFileToCDN($file, getDueTrackerAttachmentsFolder(), $filename);
                }
            }
        }
        return json_encode($attachments);
    }

    protected function getPartner($request)
    {
        $partner_id = $request->partner->id ?? (int)$request->partner;
        return Partner::find($partner_id);
    }
//TODO: should remove in next release (after pos rebuild)
    public function createPosOrderPayment($amount_cleared, $pos_order_id, $payment_method)
    {
        /** @var PosOrder $order */
        $order = PosOrder::find($pos_order_id);
        if(isset($order)) {
            $order->calculate();
            if ($order->getDue() > 0) {
                $payment_data['pos_order_id'] = $pos_order_id;
                $payment_data['amount']       = $amount_cleared;
                $payment_data['method']       = $payment_method;
                /** @var PaymentCreator $paymentCreator */
                $paymentCreator = app(PaymentCreator::class);
                $paymentCreator->credit($payment_data);
                $order->calculate();
            }
        }
    }

    public function removePosOrderPayment($pos_order_id, $amount)
    {
        $payment = PosOrderPayment::where('pos_order_id', $pos_order_id)
            ->where('amount', $amount)
            ->where('transaction_type', 'Credit')
            ->first();

        return $payment ? $payment->delete() : false;
    }

    /**
     * @param $userId
     * @return bool
     */
    public function isMigratedToAccounting($userId): bool
    {
        $arr = [self::NOT_ELIGIBLE, UserStatus::PENDING, UserStatus::UPGRADING, UserStatus::FAILED];
        /** @var UserMigrationRepository $userMigrationRepo */
        $userMigrationRepo = app(UserMigrationRepository::class);
        $userStatus = $userMigrationRepo->userStatus($userId);
        if (in_array($userStatus, $arr)) return false;
        return true;
    }
}