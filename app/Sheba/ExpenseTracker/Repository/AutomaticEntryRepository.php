<?php


namespace Sheba\ExpenseTracker\Repository;


use Carbon\Carbon;
use Exception;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\EntryType;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Exceptions\InvalidHeadException;
use Sheba\RequestIdentification;

class AutomaticEntryRepository extends BaseRepository
{
    private $head, $amount, $amount_cleared, $result, $for;

    /**
     * @param mixed $for
     * @return AutomaticEntryRepository
     */
    public function setFor($for)
    {
        $this->for = $for;
        return $this;
    }

    /**
     * @param mixed $result
     * @return AutomaticEntryRepository
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return AutomaticEntryRepository
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param $head
     * @return AutomaticEntryRepository
     * @throws InvalidHeadException
     */
    public function setHead($head)
    {
        if (!in_array($head, AutomaticIncomes::heads()) || !in_array($head, AutomaticExpense::heads()))
            throw new InvalidHeadException();
        if (in_array($head, AutomaticExpense::heads())) $this->for = EntryType::EXPENSE;
        else $this->for = EntryType::INCOME;
        $this->head = $head;
        return $this;
    }

    /**
     * @param mixed $amount_cleared
     * @return AutomaticEntryRepository
     */
    public function setAmountCleared($amount_cleared)
    {
        $this->amount_cleared = $amount_cleared;
        return $this;
    }

    /**
     * @return mixed
     * @throws ExpenseTrackingServerError
     * @throws Exception
     */
    public function store()
    {
        $data['created_at'] = Carbon::now()->format('Y-m-d H:s:i');
        $data['created_from'] = json_encode((new RequestIdentification())->get());
        $data['amount'] = $this->amount;
        $data['amount_cleared'] = $this->amount_cleared;
        $data['head'] = $this->head;
        if (empty($data['amount'])) $data['amount'] = 0;
        if (empty($data['amount_cleared'])) $data['amount_cleared'] = 0;
        $this->result = $this->client->post('accounts/' . $this->accountId . '/' . EntryType::getRoutable($this->for), $data)['data'];
        return $this->result;
    }


}
