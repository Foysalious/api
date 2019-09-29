<?php namespace Sheba\Reward\Commands;

use App\Models\Bonus;
use Illuminate\Console\Command;
use Sheba\Repositories\BonusLogRepository;

class BonusInvalidator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheba:invalidate-bonus-rewards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make bonuses invalid.';

    /**
     * Execute the console command.
     * @param BonusLogRepository $bonus_log_repo
     */
    public function handle(BonusLogRepository $bonus_log_repo)
    {
        $query = Bonus::valid()->validationDateOver();
        $invalid_amounts = $query->selectRaw('sum(amount) as amount, user_type, user_id')->groupBy('user_type', 'user_id')->get();
        $query->update(['status' => 'invalid']);
        $log_data = [];
        foreach ($invalid_amounts as $invalid_amount) {
            $log_data[] = [
                'type' => 'Debit',
                'user_type' => $invalid_amount->user_type,
                'user_id' => $invalid_amount->user_id,
                'amount' => $invalid_amount->amount,
                'valid_till' => null
            ];
        }
        $bonus_log_repo->insert($log_data);
        $this->info('Done');
    }
}