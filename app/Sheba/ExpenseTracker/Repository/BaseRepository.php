<?php namespace Sheba\ExpenseTracker\Repository;

use App\Models\Partner;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ModificationFields;
use Sheba\TopUp\TopUpAgent;

class BaseRepository
{
    use ModificationFields;

    /** @var ExpenseTrackerClient $client */
    protected $client;
    /** @var int $accountId */
    protected $accountId;
    protected $partnerId;
    /**
     * BaseRepository constructor.
     * @param ExpenseTrackerClient $client
     */
    public function __construct(ExpenseTrackerClient $client)
    {
        $this->client = $client;

    }

    /**
     * @param Partner | TopUpAgent $partner
     * @return $this
     * @throws ExpenseTrackingServerError
     */
    public function setPartner(Partner $partner)
    {
        if (!$partner->expense_account_id) {
            $this->setModifier($partner);
            $data = ['account_holder_type' => get_class($partner), 'account_holder_id' => $partner->id];
            $result = $this->client->post('accounts', $data);
            $data = ['expense_account_id' => $result['account']['id']];
            $partner->update($data);
        }
        $this->accountId = $partner->expense_account_id;
        $this->partnerId = $partner->id;
        return $this;
    }
}
