<?php namespace Sheba\Reward\Disburse;

use App\Models\PartnerOrder;
use App\Models\Job;
use App\Models\Reward;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\Repositories\BonusRepository;
use Sheba\Repositories\RewardLogRepository;
use Sheba\Reward\Event;
use Sheba\Reward\Rewardable;

class DisburseHandler
{
    /** @var RewardLogRepository */
    private $rewardRepo;
    /** @var Reward */
    private $reward;
    /** @var BonusRepository */
    private $bonusRepo;
    /** @var PointDisburse */
    private $pointDisburse;
    /** @var CashDisburse */
    private $cashDisburse;
    /** @var Event $event */
    private $event;
    /** @var PartnerOrder */
    private $partner_order;

    public function __construct(RewardLogRepository $log_repository, BonusRepository $bonus_repository, PointDisburse $point_disburse, CashDisburse $cashDisburse)
    {
        $this->rewardRepo = $log_repository;
        $this->bonusRepo = $bonus_repository;
        $this->pointDisburse = $point_disburse;
        $this->cashDisburse = $cashDisburse;
    }

    public function setReward(Reward $reward)
    {
        $this->reward = $reward;
        return $this;
    }

    public function setEvent(Event $event)
    {
        $this->event = $event;
        return $this;
    }

    public function setPartnerOrder(PartnerOrder $partner_order)
    {
        $this->partner_order = $partner_order;
        return $this;
    }

    /**
     * @param Rewardable $rewardable
     */
    public function disburse(Rewardable $rewardable)
    {
        $amount = $this->reward->getAmount();

        if ($amount > 0) $this->disburseAmount($rewardable, $amount);

        $this->storeRewardLog($rewardable);
    }

    /**
     * @param Rewardable $rewardable
     * @param $amount
     */
    private function disburseAmount(Rewardable $rewardable, $amount)
    {
        if ($this->reward->isValidityApplicable()) {
            $this->disburseAsBonus($rewardable, $amount);
        } else {
            $this->disburseAsRegular($rewardable, $amount);
        }
    }

    /**
     * @param Rewardable $rewardable
     * @param $amount
     */
    private function disburseAsBonus(Rewardable $rewardable, $amount)
    {
        $this->bonusRepo->storeFromReward($rewardable, $this->reward, $amount);
    }

    /**
     * @param Rewardable $rewardable
     * @param $amount
     */
    private function disburseAsRegular(Rewardable $rewardable, $amount)
    {
        if ($this->reward->isCashType()) {
            $this->disburseCash($rewardable, $amount);
        } elseif ($this->reward->isPointType()) {
            $this->disbursePoint($rewardable, $amount);
        }
    }

    /**
     * @param Rewardable $rewardable
     * @param $amount
     */
    private function disburseCash(Rewardable $rewardable, $amount)
    {
        $this->cashDisburse->setRewardable($rewardable);

        $log = $amount . " BDT credited for " . $this->reward->name . " reward #" . $this->reward->id;

        if ($this->partner_order) {
            $job = Job::where('partner_order_id', $this->partner_order->id)->latest()->first();
            $this->cashDisburse->creditWithJobId($amount, $log, $job->id);
        } else {
            $this->cashDisburse->credit($amount, $log);
        }

        $this->storeJournal($rewardable);
    }

    /**
     * @param Rewardable $rewardable
     * @param $amount
     */
    private function disbursePoint(Rewardable $rewardable, $amount)
    {
        $this->pointDisburse->setRewardable($rewardable)->updateRewardPoint($amount);
    }

    private function generateLog($rewardable)
    {
        $to_whom = class_basename($rewardable);

        return ($this->event)
            ? $this->event->getLogEvent()
            : $this->reward->amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on ' . $to_whom . ' id: ' . $rewardable->id;
    }

    /**
     * @param $rewardable
     */
    private function storeRewardLog($rewardable)
    {
        $reward_log = $this->generateLog($rewardable);

        $this->rewardRepo->storeLog($this->reward, $rewardable->id, $reward_log);
    }

    private function storeJournal($rewardable)
    {
        $transaction = $this->cashDisburse->getTransaction();
        if (!$transaction) return;

        (new JournalCreateRepository())
            ->setTypeId($rewardable->id)
            ->setSource($transaction)
            ->setAmount($this->reward->amount)
            ->setDebitAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
            ->setCreditAccountKey(\Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Income\Reward::REWARD)
            ->setDetails("Cash reward")
            ->setReference($this->reward->id)
            ->store();
    }
}