<?php namespace Sheba\ExpenseTracker\Repository;

use App\Models\Partner;
use Carbon\Carbon;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\Helpers\TimeFrame;
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
        if ($this->isMigratedToAccounting()) {
            return true;
        }
        $data['created_at'] = Carbon::parse($data['created_at'])->format('Y-m-d H:i:s');
        $request_identification = $this->withBothModificationFields((new RequestIdentification())->get());
        $data['created_from'] = json_encode($request_identification);
        $result = $this->client->post('accounts/' . $this->accountId . '/' . $for, $data);
        return $result['data'];
    }

    /**
     * @param $for
     * @param $data
     * @param $entry_id
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function updateEntry($for, $data, $entry_id)
    {
        if ($this->isMigratedToAccounting()) {
            return true;
        }
        $request_identification = $this->withBothModificationFields((new RequestIdentification())->get());
        $data['created_from'] = json_encode($request_identification);
        $result = $this->client->post('accounts/' . $this->accountId . '/' . $for . '/' . $entry_id, $data);

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
        $this->start_date = $start_date->format('Y-m-d H:i:s');
        return $this;
    }

    /**
     * @param Carbon $end_date
     * @return EntryRepository
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date->format('Y-m-d H:i:s');
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
     * @param $profile_id
     * @param $payable_id
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function getPayableById($profile_id, $payable_id)
    {
        $result = $this->client->get('accounts/' . $this->accountId . '/payables/' . $payable_id . '?profile_id=' . $profile_id);
        return $result['payable'];
    }

    /**
     * @param $profile_id
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function getAllPayablesByCustomer($profile_id)
    {
        return $this->client->get('accounts/' . $this->accountId . '/payables?profile_id=' . $profile_id . '&limit=' . $this->limit . '&offset=' . $this->offset);
    }

    /**
     * @param $profile_id
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function getTotalPayableAmountByCustomer($profile_id)
    {
        return $this->client->get('accounts/' . $this->accountId . '/payable-amount?profile_id=' . $profile_id);
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
        $data = ['account_holder_type' => get_class($partner), 'account_holder_id' => $partner->id];
        $result = $this->client->post('accounts', $data);
        return $result['account'];
    }

    /**
     * @param TimeFrame $time_frame
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function statsBetween(TimeFrame $time_frame)
    {
        $start = $time_frame->start->toDateString();
        $end = $time_frame->end->toDateString();
        return $this->client->get("accounts/$this->accountId/stats?start_date=$start&end_date=$end");
    }

    /**
     * @param $data
     * @param $updater_information
     * @param $payable_id
     * @return string
     * @throws ExpenseTrackingServerError
     */
    public function payPayable($data, $updater_information, $payable_id)
    {
        $data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $request_identification = (new RequestIdentification())->get() + $updater_information;
        $data['created_from'] = json_encode($request_identification);
        $result = $this->client->post('accounts/' . $this->accountId . '/payables/' . $payable_id . '/pay', $data);

        return $result['data'];
    }

    /**
     * @param $payable_id
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function getAllPayableLogsBy($payable_id)
    {
        $result = $this->client->get('accounts/' . $this->accountId . '/payables/' . $payable_id . '/logs');
        return $result['payable_logs'];
    }
}
