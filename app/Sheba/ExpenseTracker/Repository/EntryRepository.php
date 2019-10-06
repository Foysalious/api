<?php namespace Sheba\ExpenseTracker\Repository;

use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;

class EntryRepository extends BaseRepository
{
    /**
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function getAllExpenses()
    {
        $result = $this->client->get('accounts/' . $this->accountId . '/expenses');
        return $result['expenses'];
    }
}
