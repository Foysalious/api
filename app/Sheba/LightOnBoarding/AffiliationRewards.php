<?php namespace App\Sheba\LightOnBoarding;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use Sheba\ModificationFields;

class AffiliationRewards
{
    use ModificationFields;
    private $moderationCost = 60;
    private $affiliationCost = 20;
    private $moderator, $affiliate;
    private $requestIdentification;

    public function __construct()
    {

    }


    public function setAffiliate($affiliate)
    {
        if (!empty($affiliate)) {
            $this->affiliate = $affiliate;
            return $this;
        } else {
            throw new \Exception('Affiliate is not set for partner');
        }
    }

    public function setModerator(Affiliate $moderator)
    {
        $this->moderator = $moderator;
        return $this;
    }

    public function payModerator($ref, $status)
    {
        $this->moderatorWalletUpdate();
        $this->moderatorTransaction($ref, $status);
        return $this;
    }

    public function payAffiliate($ref)
    {
        $this->affiliateWalletUpdate();
        $this->affiliationTransaction($ref);
        return $this;
    }

    public function getModerationCost()
    {
        return $this->moderationCost;
    }

    public function getAffiliationCost()
    {
        return $this->affiliationCost;
    }

    public function getTotalCost()
    {
        return $this->getAffiliationCost() + $this->getModerationCost();
    }

    private function affiliationTransaction($ref)
    {
        $log = "Earned " . $this->getAffiliationCost() . " tk for giving reference partner id: $ref->id";
        $data = [
            'affiliate_id' => $this->affiliate->id,
            'affiliation_id' => $ref->id,
            'affiliation_type' => get_class($ref),
            'type' => "Credit",
            'log' => $log,
            'amount' => $this->getAffiliationCost()
        ];
        $affiliate_transaction = new AffiliateTransaction($this->withCreateModificationField($data));
        $affiliate_transaction->save();
    }

    private function moderatorTransaction($ref, $status)
    {
        if ($status == 'accept') {
            $statusText = 'accepting';
        } else {
            $statusText = 'rejecting';
        }
        $log = "Earned " . $this->getModerationCost() . "tk for $statusText  reference of partner id: $ref->id";
        $data = [
            'affiliate_id' => $this->moderator->id,
            'affiliation_id' => $ref->id,
            'affiliation_type' => get_class($ref),
            'type' => "Credit",
            'log' => $log,
            'amount' => $this->getModerationCost()
        ];
        $affiliate_transaction = new AffiliateTransaction($this->withCreateModificationField($data));
        $affiliate_transaction->save();
    }

    private function affiliateWalletUpdate()
    {
        $this->affiliate->wallet += $this->getAffiliationCost();
        $this->affiliate->update();
    }

    private function moderatorWalletUpdate()
    {
        $this->moderator->wallet += $this->getModerationCost();
        $this->moderator->update();
    }


}