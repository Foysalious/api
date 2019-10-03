<?php namespace Sheba\Reports\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Reports\ReportData;

abstract class Transaction extends ReportData
{
    protected $balances = [];

    public function get(Request $request)
    {
        $transactions = $this->getTransactions()->map(function ($transaction) {
            return $this->mapWithBalance($transaction);
        });

        $transactions = $this->filterDate($transactions, $request);

        return $transactions->map(function ($transaction) {
            return $this->mapForView($transaction);
        })->values()->all();
    }

    abstract protected function getTransactions();

    /**
     * @param $transaction
     * @return Model
     */
    protected function mapWithBalance($transaction)
    {
        list($id, $type, $amount) = $this->getFields();
        
        if (!array_key_exists($transaction->$id, $this->balances)) {
            $this->balances[$transaction->$id] = 0;
        }
        $this->balances[$transaction->$id] = $transaction->$type == "Debit" ?
            $this->balances[$transaction->$id] - $transaction->$amount :
            $this->balances[$transaction->$id] + $transaction->$amount;

        $transaction->balance = $this->balances[$transaction->$id];

        return $transaction;
    }

    abstract protected function getFields();

    /**
     * @param $transactions
     * @param Request $request
     * @return Collection
     */
    protected function filterDate($transactions, Request $request)
    {
        if ($this->isNotLifetime($request->all())) {
            $time_frame = $this->getStartEndCarbon($request);
            $start = $time_frame[0];
            $end = $time_frame[1];
            $transactions = $transactions->filter(function ($transaction) use ($start, $end) {
                return $transaction->created_at->between($start, $end);
            });
        }
        return $transactions;
    }

    /**
     * @param $transaction
     * @return mixed
     */
    abstract protected function mapForView($transaction);
}