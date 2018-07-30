<?php namespace Sheba\Bondhu;

use App\Models\Affiliate;
use App\Models\PartnerAffiliation;
use Sheba\PartnerAffiliation\PartnerAffiliationEarning;
use Sheba\Repositories\AffiliateRepository;

class AffiliateEarning implements PartnerAffiliationEarning
{
    private $affiliateRepo;

    public function __construct(AffiliateRepository $affiliate_repository)
    {
        $this->affiliateRepo = $affiliate_repository;
    }

    public function partnerAffiliation(PartnerAffiliation $affiliation, $reward)
    {
        $affiliate = $affiliation->affiliate;
        if ($affiliate->isAmbassador() || !$affiliate->ambassador_id) {
            $log = "Earned $reward tk for sp reference: " . $affiliation->partner->name . " (#$affiliation->id)";
            $this->creditWalletForPartnerAffiliation($affiliation, $reward, $affiliate, $log);
        } else {
            $ambassador = $affiliate->ambassador;
            $ambassador_reward = floatval($reward) * (floatval(constants('PARTNER_AFFILIATION_AMBASSADOR_COMMISSION'))/100);
            $log = "Earned $ambassador_reward tk for sp reference by affiliate: " . $affiliation->partner->name . " (#$affiliation->id)";
            $this->creditWalletForPartnerAffiliation($affiliation, $ambassador_reward, $ambassador, $log);

            $affiliate_reward = $reward - $ambassador_reward;
            $log = "Earned $affiliate_reward tk for sp reference: " . $affiliation->partner->name . " (#$affiliation->id)";
            $this->creditWalletForPartnerAffiliation($affiliation, $affiliate_reward, $affiliate, $log);
        }
    }

    private function creditWalletForPartnerAffiliation(PartnerAffiliation $affiliation, $reward, Affiliate $affiliate, $log)
    {
        $data = [
            'affiliation_type' => get_class($affiliation),
            'affiliation_id' => $affiliation->id,
            'type' => 'Credit',
            'log' => $log,
            'amount' => $reward
        ];
        $this->affiliateRepo->creditWallet($affiliate, $reward, $data);
    }

    public function affiliation(Affiliate $affiliate, $amount, $affiliation_id, $order_code)
    {
        $data = [
            'affiliation_type' => "App\\Models\\Affiliation",
            'affiliation_id' => $affiliation_id,
            'type' => 'Credit',
            'log' => "Earned $amount tk for reference: $affiliation_id and order: $order_code",
            'amount' => $amount
        ];
        $this->affiliateRepo->creditWallet($affiliate, $amount, $data);
    }

    public function affiliationAmbassadorEarning(Affiliate $ambassador, Affiliate $affiliate, $amount, $affiliation_id, $order_code)
    {
        $affiliate_identity = ($affiliate->name ?: $affiliate->mobile) ?: "#$affiliate->id";
        $data = [
            'affiliation_type' => "App\\Models\\Affiliation",
            'affiliation_id' => $affiliation_id,
            'type' => 'Credit',
            'log' => "$affiliate_identity gifted $amount tk for reference: $affiliation_id and order: $order_code",
            'amount' => $amount
        ];
        $this->affiliateRepo->creditWallet($ambassador, $amount, $data);
    }

    public function broadcasting(Affiliate $ambassador, $amount, $order_code)
    {
        $data = [
            'type'   => 'Credit',
            'log'    => "Earned $amount tk for order: $order_code",
            'amount' => $amount
        ];
        $this->affiliateRepo->creditWallet($ambassador, $amount, $data);
    }
}