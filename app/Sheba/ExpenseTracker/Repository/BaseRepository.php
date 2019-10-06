<?php namespace Sheba\ExpenseTracker\Repository;

use App\Models\Partner;

class BaseRepository
{
    protected $client;
    protected $accountId;

    public function __construct(ExpenseTrackerClient $client)
    {
        $this->client = $client;
    }

    public function setPartner(Partner $partner)
    {
        $this->accountId = $partner->expense_tracker_id;
        return $this;
    }
}
