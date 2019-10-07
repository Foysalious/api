<?php namespace Sheba\ExpenseTracker\Repository;

use Carbon\Carbon;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\RequestIdentification;

class EntryRepository extends BaseRepository
{
    /**
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function getAllIncomes()
    {
        $result = $this->client->get('accounts/' . $this->accountId . '/incomes');
        return $result['incomes'];
    }

    /**
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function getAllExpenses()
    {
        $result = $this->client->get('accounts/' . $this->accountId . '/expenses');
        return $result['expenses'];
    }

    /**
     * @param $for
     * @param $data
     * @return string
     * @throws ExpenseTrackingServerError
     */
    public function storeEntry($for, $data)
    {
        $data['created_at'] = Carbon::parse($data['created_at'])->format('Y-m-d H:s:i');
        $request_identification = (new RequestIdentification())->get();
        $data['created_from'] = json_encode($request_identification);
        $result = $this->client->post('accounts/' . $this->accountId . '/' . $for, $data);

        return $result['data'];
    }
}
