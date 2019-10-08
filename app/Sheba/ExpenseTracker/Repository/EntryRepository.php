<?php namespace Sheba\ExpenseTracker\Repository;

use Carbon\Carbon;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\RequestIdentification;

class EntryRepository extends BaseRepository
{
    /** @var Carbon $start_date */
    private $start_date;
    /** @var Carbon $end_date */
    private $end_date;
    /** @var int $limit */
    private $limit;
    /** @var int $offset */
    private $offset;

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
    public function getAllIncomesBetween()
    {
        $url = 'accounts/' . $this->accountId . '/incomes?start_date=' . $this->start_date . '&end_date=' . $this->end_date . '&limit=' . $this->limit . '&offset=' . $this->offset;
        return $this->client->get($url);
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

    /**
     * @param $for
     * @param $id
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function showEntry($for, $id)
    {
        $result = $this->client->get('accounts/' . $this->accountId . '/' . $for . '/' . $id);
        return $result['data'];
    }

    /**
     * @param Carbon $start_date
     * @return EntryRepository
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date->format('Y-m-d H:s:i');
        return $this;
    }

    /**
     * @param Carbon $end_date
     * @return EntryRepository
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date->format('Y-m-d H:s:i');
        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }
}
