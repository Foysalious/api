<?php namespace Sheba\PartnerWallet;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use Sheba\Notification\DataHandler;
use Sheba\PushNotification\Pusher;
use Sheba\Repositories\PartnerTransactionRepository;

class ManualAdjuster
{
    private $push;
    private $notificationData;

    private $partner;
    private $data;

    public function __construct(Pusher $push, DataHandler $notification_data)
    {
        $this->push = $push;
        $this->notificationData = $notification_data;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function makeEntry()
    {
        $amount = formatTaka($this->data['amount']);

        /** @var PartnerTransaction $transaction */
        $transaction = (new PartnerTransactionRepository($this->partner))->save([
            'type' => $this->data['type'],
            'amount' => $amount,
            'log' => $this->data['log']
        ], $this->data['tag_list']);

        notify()->partner($this->partner)->send(
            $this->notificationData->manualWalletEntryToManager($transaction, $this->partner)
        );

        $push_data = $this->notificationData->manualWalletEntryPushToManager($transaction);
        $this->push->to($this->partner, $push_data);
    }
}