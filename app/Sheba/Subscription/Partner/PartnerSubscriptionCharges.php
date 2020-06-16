<?php namespace App\Sheba\Subscription\Partner;

use App\Sheba\Repositories\PartnerSubscriptionChargesRepository;
use Carbon\Carbon;
use Sheba\Subscription\Partner\PartnerSubscriptionBilling;

class PartnerSubscriptionCharges
{
    private $partnerSubscriptionBilling;
    private $action;
    private $actions;
    private $data = [];

    public function __construct(PartnerSubscriptionBilling $partnerSubscriptionBilling)
    {
        $this->partnerSubscriptionBilling = $partnerSubscriptionBilling;
        $this->actions = PartnerSubscriptionChange::all();
    }

    public function shootLog($action)
    {
        $this->action = $action;
        $this->data['action'] = $this->action;
        $this->data['partner_id'] = $this->partnerSubscriptionBilling->partner->id;

        $this->setPackage()->setPrices()->setDates()->setLog()->save();
    }
    private function
    private function setPackage()
    {
        if ($this->action == $this->actions[PartnerSubscriptionChange::RENEWED]) {
            $this->data['package_from'] = $this->data['package_to'] = $this->partnerSubscriptionBilling->partner->subscription->name . '-' . $this->partnerSubscriptionBilling->partner->billing_type;
        } else {
            $this->data['package_from'] = $this->partnerSubscriptionBilling->packageFrom->name . '-' . $this->partnerSubscriptionBilling->partner->billing_type;
            $this->data['package_to'] = $this->partnerSubscriptionBilling->packageTo->name . '-' . $this->partnerSubscriptionBilling->partner->billing_type;
        }
        return $this;
    }

    private function setPrices()
    {
        $this->data['package_price'] = $this->partnerSubscriptionBilling->packagePrice;
        $this->data['cash_wallet_charge'] = $this->partnerSubscriptionBilling->partnerBonusHandler->payFromWallet;
        $this->data['bonus_wallet_charge'] = $this->partnerSubscriptionBilling->partnerBonusHandler->payFromBonus;
        $this->data['refunded'] = $this->partnerSubscriptionBilling->refundAmount;
        return $this;
    }

    private function setDates()
    {
        $this->data['activation_date'] = Carbon::parse($this->partnerSubscriptionBilling->partner->billing_start_date);
        $this->data['billing_date'] = $this->partnerSubscriptionBilling->today;
        return $this;
    }

    private function setLog()
    {
        $this->data['log'] = 'Subscription packages ' . $this->findActionText() . ' from ' . $this->data['package_from'] . ' to ' . $this->data['package_to'];
        return $this;
    }

    private function findActionText()
    {
        foreach ($this->actions as $key => $value) {
            if ($value == $this->action) {
                return $key;
            }
        }
        return '';
    }

    private function save()
    {
        $repo = new PartnerSubscriptionChargesRepository();
        return $repo->create($this->data);
    }
}
