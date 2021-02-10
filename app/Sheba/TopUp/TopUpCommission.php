<?php namespace Sheba\TopUp;

use App\Models\Affiliate;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use App\Sheba\Transactions\Wallet\RobiTopUpWalletTransactionHandler;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Dal\TopUpOTFSettings\Contract as TopUpOTFSettingsRepo;
use Sheba\Dal\TopUpVendorOTF\Contract as TopUpVendorOTFRepo;

abstract class TopUpCommission
{
    use ModificationFields;

    /** @var TopUpOrder */
    protected $topUpOrder;
    /** @var TopUpAgent */
    protected $agent;
    /** @var TopUpVendor */
    protected $vendor;
    /** @var TopUpVendorCommission */
    protected $vendorCommission;
    protected $amount;

    /**
     * @param TopUpOrder $top_up_order
     * @return $this
     */
    public function setTopUpOrder(TopUpOrder $top_up_order)
    {
        $this->topUpOrder = $top_up_order;
        $this->amount = $this->topUpOrder->amount;

        $this->setAgent($top_up_order->agent)->setTopUpVendor($top_up_order->vendor)->setVendorCommission();

        unset($top_up_order->agent);
        unset($top_up_order->vendor);

        return $this;
    }

    /**
     * @return TopUpCommission
     */
    protected function setVendorCommission()
    {
        $commissions = $this->vendor->commissions()->where('type', get_class($this->agent));
        $commissions_copy = clone $commissions;
        $commission_of_individual = $commissions_copy->where('type_id', $this->agent->id)->first();
        $this->vendorCommission = $commission_of_individual ?: $commissions->whereNull('type_id')->first();
        return $this;
    }

    /**
     * @param TopUpVendor $top_up_order
     * @return TopUpCommission
     */
    protected function setTopUpVendor(TopUpVendor $top_up_order)
    {
        $this->vendor = $top_up_order;
        return $this;
    }

    /**
     * @param TopUpAgent $agent
     * @return TopUpCommission
     */
    protected function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    abstract public function disburse();

    abstract public function refund();

    /**
     *
     */
    protected function storeAgentsCommission()
    {
        $this->topUpOrder->agent_commission = $this->calculateCommission($this->topUpOrder->amount);
        $otf_details = $this->getVendorOTFDetails($this->topUpOrder->vendor_id, $this->topUpOrder->amount, $this->topUpOrder->gateway, $this->topUpOrder->payee_mobile_type);

        $this->topUpOrder->otf_id = isset($otf_details['otf_id']) ? $otf_details['otf_id'] : 0;
        $this->topUpOrder->otf_agent_commission = isset($otf_details['agent_commisssion']) ? $otf_details['agent_commisssion'] : 0;
        $this->topUpOrder->otf_sheba_commission = isset($otf_details['sheba_commisssion']) ? $otf_details['sheba_commisssion'] : 0;
        $this->topUpOrder->save();

        if($this->topUpOrder->otf_agent_commission > 0){
            $log_message = $this->amount . "tk " . $otf_details['otf_name'] . " - OTF TopUp has been recharged to " . $this->topUpOrder->payee_mobile;
        }else{
            $log_message = $this->amount . " has been topped up to " . $this->topUpOrder->payee_mobile;
        }

        $transaction = (new TopUpTransaction())
            ->setAmount($this->amount - $this->topUpOrder->agent_commission - $this->topUpOrder->otf_agent_commission)
            ->setLog($log_message)
            ->setTopUpOrder($this->topUpOrder)
            ->setIsRobiTopUp($this->topUpOrder->isRobiWalletTopUp());
        $this->agent->topUpTransaction($transaction);
    }

    /**
     * @param $amount
     * @return float|int
     */
    protected function calculateCommission($amount)
    {
        $commission = (double)$amount * ($this->getVendorAgentCommission() / 100);
        if ($this->agent instanceof Affiliate) {
            $cap = constants('AFFILIATE_REWARD')['TOP_UP']['AGENT']['cap'];
            return min($commission, $cap);
        }
        return $commission;
    }

    /**
     * @return float
     */
    private function getVendorAgentCommission()
    {
        return (double)$this->vendorCommission->agent_commission;
    }

    /**
     * @param $amount
     * @return float|int
     */
    protected function calculateAmbassadorCommission($amount)
    {
        $commission = (double)$amount * ($this->getVendorAmbassadorCommission() / 100);
        if ($this->agent instanceof Affiliate) {
            $cap = constants('AFFILIATE_REWARD')['TOP_UP']['AMBASSADOR']['cap'];
            return min($commission, $cap);
        }
        return $commission;
    }

    /**
     * @return float
     */
    private function getVendorAmbassadorCommission()
    {
        return (double)$this->vendorCommission->ambassador_commission;
    }

    protected function refundAgentsCommission()
    {
        $this->setModifier($this->agent);
        $amount = $this->topUpOrder->amount;
        $amount_after_commission = round($amount - $this->calculateCommission($amount), 2);
        $log = "Your recharge TK $amount to {$this->topUpOrder->payee_mobile} has failed, TK $amount_after_commission is refunded in your account.";
        $this->refundUser($amount_after_commission, $log,$this->topUpOrder->isRobiWalletTopUp());
    }

    private function refundUser($amount, $log,$isRobiTopUp=false)
    {
        if ($amount == 0) return;
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         *  $this->agent->creditWallet($amount);
         $this->agent->walletTransaction(['amount' => $amount, 'type' => 'Credit', 'log' => $log]);*/
        /** @var HasWalletTransaction $model */
        $model = $this->agent;
        if(!$isRobiTopUp)
        (new WalletTransactionHandler())->setModel($model)->setSource(TransactionSources::TOP_UP)->setType(Types::credit())
            ->setAmount($amount)->setLog($log)->dispatch();
        if($isRobiTopUp)
            (new RobiTopupWalletTransactionHandler())->setModel($model)->setAmount($amount)->setLog($log)->setType(Types::credit())->store();

    }

    private function getVendorOTFDetails($vendor_id, $amount, $gateway, $con_type)
    {
        $otf_details = [];

        $topupotfsettings = app(TopUpOTFSettingsRepo::class);
        $topupvendorotf = app(TopUpVendorOTFRepo::class);

        $agent_fullname = null;

        if ($this->agent instanceof Affiliate){

            $agent_fullname = 'App\\Models\\Affiliate';

        }elseif ($this->agent instanceof Partner) {

            $agent_fullname = 'App\\Models\\Partner';

        }elseif ($this->agent instanceof Customer){

            $agent_fullname = 'App\\Models\\Customer';

        }

        if ($agent_fullname){
            $otf_settings = $topupotfsettings->builder()->where([['topup_vendor_id', $vendor_id], ['type', $agent_fullname]])->first();

            if($otf_settings->applicable_gateways != 'null' && in_array($gateway, json_decode($otf_settings->applicable_gateways)) == true){

                $otf_exists = $topupvendorotf->builder()->where('topup_vendor_id', $vendor_id)->where('amount', $amount)->where('sim_type', 'like', '%' . $con_type . '%')->where('status', 'Active')->first();

                if($otf_exists){
                    $agen_comm = round(($otf_settings->agent_commission / 100) * $otf_exists->cashback_amount, 2);

                    $otf_details['otf_id'] = $otf_exists->id;
                    $otf_details['agent_commisssion'] = $agen_comm;
                    $otf_details['sheba_commisssion'] = $otf_exists->cashback_amount - $agen_comm;
                    $otf_details['otf_name'] = $otf_exists->name_en;
                }
            }
        }

        return $otf_details;
    }
}
