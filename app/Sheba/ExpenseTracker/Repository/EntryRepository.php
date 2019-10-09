<?php namespace Sheba\ExpenseTracker\Repository;

use App\Models\Partner;
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
    public function getAllExpensesBetween()
    {
        $url = 'accounts/' . $this->accountId . '/expenses?start_date=' . $this->start_date . '&end_date=' . $this->end_date . '&limit=' . $this->limit . '&offset=' . $this->offset;
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
     * @param $data
     * @param $entryId
     * @return string
     * @throws ExpenseTrackingServerError
     */
    public function updateEntry($for, $data, $entryId)
    {
        $request_identification = (new RequestIdentification())->get();
        $data['created_from'] = json_encode($request_identification);
        $result = $this->client->post('accounts/' . $this->accountId . '/' . $for . '/' . $entryId, $data);
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

    /**
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function getAllPayables()
    {
        return $this->client->get('accounts/' . $this->accountId . '/payables' . '?limit=' . $this->limit . '&offset=' . $this->offset);
    }

    /**
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function getAllReceivables()
    {
        return $this->client->get('accounts/' . $this->accountId . '/receivables' . '?limit=' . $this->limit . '&offset=' . $this->offset);
    }

    /**
     * @param int $for
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function getHeads($for)
    {
        return $this->client->get('accounts/' . $this->accountId . '/heads' . '?for=' . $for);
    }

    /**
     * @param Partner $partner
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function createExpenseUser(Partner $partner)
    {
        $data['partner_id'] = $partner->id;
        $result = $this->client->post('accounts', $data);
        return $result['account'];
    }
}