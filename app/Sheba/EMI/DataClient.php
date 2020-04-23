<?php namespace Sheba\EMI;


use App\Models\Partner;
use GuzzleHttp\Client;
use Sheba\ExpenseTracker\Repository\ExpenseTrackerClient;

class DataClient {
    /** @var ExpenseTrackerClient $client */
    private $client;
    /** @var Partner $partner */
    private $partner;
    private $baseUrl;

    public function __construct(Partner $partner) {
        $this->partner = $partner;
        $this->client  = new ExpenseTrackerClient(new Client());
        $this->baseUrl = 'accounts/' . $this->partner->expense_account_id;
    }

    public function emiList() {
        try {
            if ($this->partner->expense_account_id) {
                return $this->client->get($this->baseUrl . "/entries/emi");
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return [];
        }
        return [];
    }
}
