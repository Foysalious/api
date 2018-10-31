<?php namespace Sheba\Repositories;

use App\Models\Bonus;
use App\Models\Reward;
use Carbon\Carbon;
use Sheba\Reward\Rewardable;

class BonusRepository extends BaseRepository
{
    /**
     * @var BonusLogRepository
     */
    private $logRepository;

    /**
     * BonusRepository constructor.
     * @param BonusLogRepository $logRepository
     */
    public function __construct(BonusLogRepository $logRepository)
    {
        parent::__construct();
        $this->logRepository = $logRepository;
    }

    /**
     * @param Rewardable $rewardable
     * @param Reward $reward
     * @param $amount
     */
    public function store(Rewardable $rewardable, Reward $reward, $amount)
    {
        $data = [
            'user_type' => get_class($rewardable),
            'user_id'   => $rewardable->id,
            'type'      => $reward->type,
            'amount'    => $amount,
            'log'       => $reward->name,
            'valid_till'=> $this->validityCalculator($reward)
        ];

        Bonus::create($this->withCreateModificationField($data));

        if ($reward->isCashType()) {
            $log_data = array_except($data, ['type']);
            $log_data['type'] = "Credit";
            $this->logRepository->store($log_data);
        }
    }

    /**
     * @param Reward $reward
     * @return Carbon|mixed|null
     */
    private function validityCalculator(Reward $reward)
    {
        $valid_till = null;

        if ($reward->valid_till_date) {
            $valid_till = $reward->valid_till_date;
        } elseif ($reward->valid_till_day) {
            $valid_till = Carbon::now()->addDays($reward->valid_till_day);
        }

        return $valid_till->endOfDay();
    }
}