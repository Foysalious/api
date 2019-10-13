<?php namespace Sheba\ExpenseTracker\Repository;

use Carbon\Carbon;
use Exception;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\EntryType;
use Sheba\ExpenseTracker\Exceptions\InvalidHeadException;
use Sheba\RequestIdentification;
use Throwable;

class AutomaticEntryRepository extends BaseRepository
{
    private $head;
    private $amount;
    private $amount_cleared;
    private $result;
    private $for;

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
     */
    public function setHead($head)
    {
        try {
            $this->validateHead($head);
            $this->head = $head;
            return $this;
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return $this;
        }
    }

    /**
     * @param $head
     * @throws InvalidHeadException
     */
    private function validateHead($head)
    {
        if (!in_array($head, AutomaticIncomes::heads()) && !in_array($head, AutomaticExpense::heads()))
            throw new InvalidHeadException();

        if (in_array($head, AutomaticExpense::heads())) $this->for = EntryType::EXPENSE;
        else $this->for = EntryType::INCOME;
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
     */
    private function getData()
    {
        $data = [
            'created_at' => Carbon::now()->format('Y-m-d H:s:i'),
            'created_from' => json_encode((new RequestIdentification())->get()),
            'amount' => $this->amount,
            'amount_cleared' => $this->amount_cleared,
            'head_name' => $this->head,
            'note' => 'Automatically Placed from Sheba'
        ];
        if (empty($data['amount'])) $data['amount'] = 0;
        if (empty($data['amount_cleared'])) $data['amount_cleared'] = $data['amount'];

        return $data;
    }

    /**
     * @return bool
     */
    public function store()
    {
        try {
            $data = $this->getData();
            if (empty($data['head_name'])) {
                throw new Exception('Head is not set before storing');
            }
            $this->result = $this->client->post('accounts/' . $this->accountId . '/' . EntryType::getRoutable($this->for), $data)['data'];
            return $this->result;
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }
}
