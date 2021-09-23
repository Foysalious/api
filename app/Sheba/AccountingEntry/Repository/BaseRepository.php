<?php namespace App\Sheba\AccountingEntry\Repository;


use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\AccountingEntry\Repository\UserMigrationRepository;
use Sheba\Dal\AccountingMigratedUser\UserStatus;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Pos\Payment\Creator as PaymentCreator;

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
            $request->cutomer_mobile = $partner_pos_customer->details()["phone"];
            $request->cutomer_pro_pic = $partner_pos_customer->details()["image"];
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
        if(isset($request->partner->id)) {
            $partner_id = $request->partner->id;
        } else {
            $partner_id = (int) $request->partner;
        }
        return Partner::find($partner_id);
    }

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
            }
        }
    }

    public function removePosOrderPayment($pos_order_id, $amount){
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
    public function isMigratedToAccounting($userId)
    {
        /** @var UserMigrationRepository $userMigrationRepo */
        $userMigrationRepo = app(UserMigrationRepository::class);
        $userStatus = $userMigrationRepo->userStatus($userId);
        if (!$userStatus) return false;
        if ($userStatus == UserStatus::PENDING) return false;
        return true;
    }
}