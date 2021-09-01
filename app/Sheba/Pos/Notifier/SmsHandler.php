<?php namespace Sheba\Pos\Notifier;

use App\Repositories\SmsHandler as SmsHandlerRepo;
use Exception;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Throwable;

class SmsHandler {

    private $data;

    public function setData($data) {
        $this->data =$data;
        return $this;
    }

    public function handle()
    {
        $sms = $this->getSms();
        $sms_cost = $sms->getCost();
        if ((double)$this->data['wallet'] > (double)$sms_cost) {
            /** @var WalletTransactionHandler $walletTransactionHandler */
            try {
                $sms->setBusinessType($this->data['business_type'])->setFeatureType($this->data['feature_type'])->shoot();
            } catch (Throwable $e) {
            }
            (new WalletTransactionHandler())->setModel($this->data['model'])->setAmount($sms_cost)->setType(Types::debit())
                ->setLog($sms_cost . $this->data['log'])->setTransactionDetails([])->setSource(TransactionSources::SMS)->store();
        }
    }

    /**
     * @return SmsHandlerRepo
     * @throws Exception
     */
    private function getSms()
    {
        return (new SmsHandlerRepo($this->data['template']))
            ->setVendor($this->data['vendor'])->setMobile($this->data['mobile'])->setFeatureType($this->data['feature_type'])
            ->setBusinessType($this->data['business_type'])->setMessage($this->data['message']);
    }
}
