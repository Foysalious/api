<?php namespace Sheba\TopUp\History;

use Carbon\Carbon;
use phpseclib\System\SSH\Agent;
use Sheba\TopUp\TopUpAgent;

class RequestBuilder
{
    private $vendorId;
    private $status;
    private $isRobiTopupWallet;
    private $searchQuery;
    private $limit;
    private $offset;
    /** @var TopUpAgent $agent */
    private $agent;
    private $from;
    private $to;
    private $connectionType;
    private $isSingleTopup;
    private $bulkId;

    /**
     * @return mixed
     */
    public function getFromDate()
    {
        return $this->from;
    }

    /**
     * @param Carbon $from
     * @return $this
     */
    public function setFromDate(Carbon $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->vendorId;
    }

    /**
     * @param $vendor_id
     * @return RequestBuilder
     */
    public function setVendorId($vendor_id)
    {
        $this->vendorId = $vendor_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return RequestBuilder
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsRobiTopupWallet()
    {
        return $this->isRobiTopupWallet;
    }

    /**
     * @param $is_robi_topup_wallet
     * @return RequestBuilder
     */
    public function setIsRobiTopupWallet($is_robi_topup_wallet)
    {
        $this->isRobiTopupWallet = $is_robi_topup_wallet;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    /**
     * @param $search_query
     * @return $this
     */
    public function setSearchQuery($search_query)
    {
        $this->searchQuery = $search_query;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param mixed $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return TopUpAgent
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param TopUpAgent $agent
     * @return $this
     */
    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToDate()
    {
        return $this->to;
    }

    /**
     * @param Carbon $to
     * @return $this
     */
    public function setToDate(Carbon $to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @param $connection_type
     * @return $this
     */
    public function setConnectionType($connection_type)
    {
        $this->connectionType = $connection_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConnectionType()
    {
        return $this->connectionType;
    }

    public function setIsSingleTopup($is_single_topup)
    {
        $this->isSingleTopup = $is_single_topup;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsSingleTopup()
    {
        return $this->isSingleTopup;
    }

    public function setBulkRequestId($bulk_id)
    {
        $this->bulkId = $bulk_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBulkId()
    {
        return $this->bulkId;
    }
}
