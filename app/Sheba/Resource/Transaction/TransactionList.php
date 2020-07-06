<?php namespace Sheba\Resource\Transaction;

use App\Models\Resource;

class TransactionList
{
    private $limit;
    private $offset;
    private $month;
    private $year;
    /** @var Resource */
    private $resource;

    /**
     * TransactionList constructor.
     */
    public function __construct()
    {
        $this->limit = 100;
        $this->offset = 0;
    }

    /**
     * @param Resource $resource
     * @return TransactionList
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @param int $month
     * @return TransactionList
     */
    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    /**
     * @param int $year
     * @return TransactionList
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @param int $limit
     * @return TransactionList
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return TransactionList
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function get()
    {
        $transactions = $this->resource->transactions()->select('id', 'type', 'amount', 'log', 'created_at')->orderBy('created_at', 'desc');
        if ($this->month && $this->year) {
            $transactions = $transactions->whereMonth('created_at','=', $this->month)->whereYear('created_at', '=', $this->year);
        }
        $transactions = $transactions->skip($this->offset)->take($this->limit)->get();
        return $transactions;
    }
}