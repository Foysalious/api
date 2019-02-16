<?php namespace App\Sheba\LightOnBoarding;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;

use Sheba\ModificationFields;

class AffiliationRewards
{
    use ModificationFields;

    private $moderationCost = 10;
    private $affiliationCost = 10;
    private $ambassadorCost = 10;
    private $moderator, $affiliate, $ambassador;
    private $requestIdentification;

    public function __construct()
    {
        $this->moderationCost = constants('AFFILIATION_LITE_ONBOARD_MODERATION_REWARD');
        $this->affiliationCost = constants('AFFILIATION_LITE_ONBOARD_REWARD');
        $this->ambassadorCost = constants('AFFILIATION_LITE_ONBOARD_AMBASSADOR_REWARD');
    }

    public function setAffiliate($affiliate)
    {
        if (!empty($affiliate)) {
            $this->affiliate = $affiliate;
            $this->ambassador = $affiliate->ambassador;
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
        $this->payAmbassador($ref);
        return $this;
    }

    private function payAmbassador($ref)
    {
        if ($this->ambassador) {
            $this->ambassadorWalletUpdate();
            $this->affiliationAmbassadorTransaction($ref);
        }
    }

    public function getModerationCost()
    {
        return $this->moderationCost;
    }

    public function getAffiliationCost()
    {
        return $this->affiliationCost;
    }

    public function getAmbassadorCost()
    {
        return $this->ambassadorCost;
    }

    public function getTotalCost()
    {
        return $this->getAffiliationCost() + $this->getModerationCost() + $this->getAmbassadorCost();
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

    private function affiliationAmbassadorTransaction($ref)
    {
        $affiliate_identity = ($this->affiliate->name ?: $this->affiliate->mobile) ?: "#$this->affiliate->id";
        $data = [
            'affiliation_type' => get_class($ref),
            'affiliation_id' => $ref->id,
            'type' => 'Credit',
            'is_gifted' => 1,
            'affiliate_id' => $this->ambassador->id,
            'log' => $affiliate_identity . 'gifted ' . $this->getAmbassadorCost() . ' tk for reference a partner, id: ' . $ref->id . ' ',
            'amount' => $this->getAmbassadorCost()
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

    private function ambassadorWalletUpdate()
    {
        $this->ambassador->wallet += $this->getAmbassadorCost();
        $this->ambassador->update();
    }
}
