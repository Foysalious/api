<?php namespace Sheba\Reward\Disburse;

use App\Models\Customer;
use App\Models\Partner;
use App\Models\Resource;
use App\Models\Reward;

use Exception;
use Sheba\CustomerWallet\CustomerTransactionHandler;
use Sheba\PartnerWallet\PartnerTransactionHandler;
use Sheba\Repositories\BonusRepository;
use Sheba\Repositories\CustomerRepository;
use Sheba\Repositories\PartnerRepository;
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

    /**
     * @param Rewardable $rewardable
     * @throws Exception
     */
    public function disburse(Rewardable $rewardable)
    {
        $amount = $this->reward->getAmount();
        if ($amount > 0) {
            if ($this->reward->isValidityApplicable()) {
                $this->bonusRepo->storeFromReward($rewardable, $this->reward, $amount);
            } else {
                if ($this->reward->isCashType()) {
                    $log = $amount . " BDT credited for " . $this->reward->name . " reward #" . $this->reward->id;

                    if ($rewardable instanceof Partner) {
                        (new PartnerTransactionHandler($rewardable))->credit($amount, $log);
                    } elseif ($rewardable instanceof Customer) {
                        (new CustomerTransactionHandler($rewardable))->credit($amount, $log);
                    } else {
                        $this->cashDisburse->setRewardable($rewardable)->credit($amount, $log);
                    }

                } elseif ($this->reward->isPointType()) {
                    if ($rewardable instanceof Partner) {
                        (new PartnerRepository())->updateRewardPoint($rewardable, $amount);
                    } elseif ($rewardable instanceof Customer) {
                        (new CustomerRepository())->updateRewardPoint($rewardable, $amount);
                    } else {
                        $this->pointDisburse->setRewardable($rewardable)->updateRewardPoint($amount);
                    }
                }
            }
        }

        $reward_log = $this->generateLog($rewardable);
        $this->storeRewardLog($rewardable, $reward_log);
    }

    /**
     * @param $rewardable
     * @param $log
     */
    private function storeRewardLog($rewardable, $log)
    {
        $this->rewardRepo->storeLog($this->reward, $rewardable->id, $log);
    }

    private function generateLog($rewardable)
    {
        if ($this->event) $reward_log = $this->event->getLogEvent();
        else $reward_log = $this->reward->amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on partner id: ' . $rewardable->id;

        return $reward_log;
    }
}