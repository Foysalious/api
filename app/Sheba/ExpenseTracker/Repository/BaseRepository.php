<?php namespace Sheba\ExpenseTracker\Repository;

use App\Models\Partner;

class BaseRepository
{
    /** @var ExpenseTrackerClient $client */
    protected $client;
    /** @var int $accountId */
    protected $accountId;

    /**
     * BaseRepository constructor.
     * @param ExpenseTrackerClient $client
     */
    public function __construct(ExpenseTrackerClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param Partner $partner
     * @return $this
     */
    public function setPartner(Partner $partner)
    {
        $this->accountId = $partner->expense_account_id;
        return $this;
    }
}
