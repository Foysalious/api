<?php namespace Sheba\PartnerOrderRequest;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequestRepositoryInterface;
use Sheba\Dal\PartnerOrderRequest\Statuses;
use Sheba\Jobs\JobLogsCreator;
use Sheba\Jobs\JobStatuses;

class UpdateNotRespondedRequests extends Command
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'sheba:update-not-responded-partner-order-requests';

    /** @var string The console command description. */
    protected $description = 'Change status of not responded partner order requests.';

    /** @var PartnerOrderRequestRepositoryInterface */
    private $repo;

    public function __construct(PartnerOrderRequestRepositoryInterface $repo)
    {
        $this->repo = $repo;
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $update_job_ids = [];
        $q = $this->makeQuery();
        $this->repo->updateByQuery($q, ['status' => Statuses::NOT_RESPONDED]);
        $q->get()->each(function (PartnerOrderRequest $request) use (&$update_job_ids) {
            $is_all_declined_or_not_responded = $request->partnerOrder->requests
                ->filter(function (PartnerOrderRequest $request) {
                    return !in_array($request->status, [Statuses::DECLINED. Statuses::NOT_RESPONDED]);
                })->count() == 0;

            if($is_all_declined_or_not_responded) {
                $update_job_ids[$request->partnerOrder->activeJob()->id] = $request->partner_id;
            }
        });

        Job::whereIn('id', array_values($update_job_ids))->update(['status' => JobStatuses::NOT_RESPONDED]);
        (new JobLogsCreator(new Job()))->noResponseLogForMultiple($update_job_ids);

        $this->info("All good");
    }

    private function makeQuery()
    {
        return PartnerOrderRequest::select('id', 'created_at', 'partner_order_id', 'partner_id')
            ->with('partnerOrder.requests', 'partnerOrder.jobs')
            ->where('status', Statuses::PENDING)
            ->where('created_at', '>', Carbon::now()->subMinutes(10));
    }
}
