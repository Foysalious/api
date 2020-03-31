<?php namespace Sheba\Bondhu;

use App\Models\Affiliate;
use App\Models\PartnerAffiliation;
use Sheba\FraudDetection\TransactionSources;
use Sheba\PartnerAffiliation\PartnerAffiliationEarning;
use Sheba\Repositories\AffiliateRepository;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class AffiliateEarning implements PartnerAffiliationEarning
{
    private $affiliateRepo;

    public function __construct(AffiliateRepository $affiliate_repository)
    {
        $this->affiliateRepo = $affiliate_repository;
    }

    public function partnerAffiliation(PartnerAffiliation $affiliation, $reward)
    {
        $affiliate         = $affiliation->affiliate;
        $ambassador_reward = floatval($reward) * (floatval(constants('PARTNER_AFFILIATION_AMBASSADOR_COMMISSION')) / 100);
        $affiliate_reward  = $reward - $ambassador_reward;
        if ($affiliate->isAmbassador() || !$affiliate->ambassador_id) {
            $log = "Earned $affiliate_reward point for sp reference: " . $affiliation->partner->name . " (#$affiliation->id)";
            $this->creditWalletForPartnerAffiliation($affiliation, $affiliate_reward, $affiliate, $log);
        } else {
            $ambassador = $affiliate->ambassador;
            $log        = "Earned $ambassador_reward point for sp reference by affiliate: " . $affiliate->name . " (#$affiliation->id)";
            $is_gifted  = 1;
            $this->creditWalletForPartnerAffiliation($affiliation, $ambassador_reward, $ambassador, $log, $is_gifted);

            $log = "Earned $affiliate_reward point for sp reference: " . $affiliation->partner->name . " (#$affiliation->id)";
            $this->creditWalletForPartnerAffiliation($affiliation, $affiliate_reward, $affiliate, $log);
        }
    }

    private function creditWalletForPartnerAffiliation(PartnerAffiliation $affiliation, $reward, Affiliate $affiliate, $log, $is_gifted = 0)
    {
        /**
         * WALLET TRANSACTION NEED TO REMOVE
         * $data = [
         * 'affiliation_type' => get_class($affiliation),
         * 'affiliation_id' => $affiliation->id,
         * 'type' => 'Credit',
         * 'log' => $log,
         * 'is_gifted' => $is_gifted,
         * 'amount' => $reward
         * ];
         * $this->affiliateRepo->creditWallet($affiliate, $reward, $data);*/
        (new WalletTransactionHandler())
            ->setModel($affiliate)
            ->setSource(TransactionSources::SHEBA_WALLET)
            ->setType('credit')
            ->setAmount($reward)
            ->setLog($log)
            ->dispatch([
                'is_gifted'        => $is_gifted,
                'affiliation_type' => get_class($affiliation),
                'affiliation_id'   => $affiliation->id
            ]);
    }

    public function affiliation(Affiliate $affiliate, $amount, $affiliation_id, $order_code)
    {
        /**
         *WALLET TRANSACTION NEED TO REMOVE
         * $data = [
         * 'affiliation_type' => "App\\Models\\Affiliation",
         * 'affiliation_id' => $affiliation_id,
         * 'type' => 'Credit',
         * 'log' => "Earned $amount tk for reference: $affiliation_id and order: $order_code",
         * 'amount' => $amount
         * ];
         * $this->affiliateRepo->creditWallet($affiliate, $amount, $data);*/
        $log = "Earned $amount point for reference: $affiliation_id and order: $order_code";
        (new WalletTransactionHandler())
            ->setModel($affiliate)
            ->setSource(TransactionSources::SHEBA_WALLET)
            ->setType('credit')
            ->setAmount($amount)
            ->setLog($log)
            ->dispatch([
                'affiliation_type' => "App\\Models\\Affiliation",
                'affiliation_id'   => $affiliation_id
            ]);
    }

    public function affiliationAmbassadorEarning(Affiliate $ambassador, Affiliate $affiliate, $amount, $affiliation_id, $order_code)
    {
        /**
         * WALLET TRANSACTION NEED TO REMOVE
         *  $data = [
         * 'affiliation_type' => "App\\Models\\Affiliation",
         * 'affiliation_id' => $affiliation_id,
         * 'type' => 'Credit',
         * 'is_gifted' => 1,
         * 'log' => "$affiliate_identity gifted $amount tk for reference: $affiliation_id and order: $order_code",
         * 'amount' => $amount
         * ];
         * $this->affiliateRepo->creditWallet($ambassador, $amount, $data);*/

        $affiliate_identity = ($affiliate->name ?: $affiliate->mobile) ?: "#$affiliate->id";
        $log                = "$affiliate_identity gifted $amount point for reference: $affiliation_id and order: $order_code";

        (new WalletTransactionHandler())
            ->setModel($affiliate)
            ->setSource(TransactionSources::SHEBA_WALLET)
            ->setType('credit')
            ->setAmount($amount)
            ->setLog($log)
            ->dispatch([
                'affiliation_type' => "App\\Models\\Affiliation",
                'is_gifted'        => 1,
                'affiliation_id'   => $affiliation_id
            ]);
    }

    public function broadcasting(Affiliate $ambassador, $amount, $order_code)
    {
        /**
         * WALLET TRANSACTION NEED TO REMOVE
         * $data = [
         * 'type' => 'Credit',
         * 'log' => "Earned $amount tk for order: $order_code",
         * 'amount' => $amount
         * ];
         * $this->affiliateRepo->creditWallet($ambassador, $amount, $data);*/
        $log = "Earned $amount point for order: $order_code";
        (new WalletTransactionHandler())->setModel($ambassador)->setSource(TransactionSources::SHEBA_WALLET)->setType('credit')->setAmount($amount)->setLog($log)->dispatch();
    }
}
