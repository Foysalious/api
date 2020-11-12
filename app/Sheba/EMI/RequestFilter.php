<?php namespace Sheba\EMI;


use Illuminate\Http\Request;
use Sheba\TopUp\Commission\Partner;

class RequestFilter {
    private $request, $limit, $offset, $recent, $partner, $q;

    /**
     * @return mixed
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * @return mixed
     */
    public function getOffset() {
        return $this->offset;
    }

    /**
     * @return mixed
     */
    public function isRecent() {
        return !empty($this->recent);
    }

    /**
     * @param mixed $limit
     * @return RequestFilter
     */
    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param mixed $offset
     * @return RequestFilter
     */
    public function setOffset($offset) {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param mixed $recent
     * @return RequestFilter
     */
    public function setRecent($recent) {
        $this->recent = $recent;
        return $this;
    }

    /**
     * @return Partner
     */
    public function getPartner() {
        return $this->partner;
    }

    public function hasQuery() {
        return $this->request->has('q') && !empty($this->request->q);
    }

    public function getQuery() {
        return trim($this->request->q);
    }

    /**
     * @param mixed $partner
     * @return RequestFilter
     */
    public function setPartner($partner) {
        $this->partner = $partner;
        return $this;
    }

    private function __init() {
        list($offset, $limit) = calculatePagination($this->request);
        $this->setLimit($limit);
        $this->setOffset($offset);
        $this->setPartner($this->request->partner);
        $this->setRecent($this->request->recent);
    }

    public function __construct() {
        $this->request = request();
        $this->__init();
    }

    public static function get() {
        return new RequestFilter();
    }

    public function original() {
        return $this->request;
    }

    public function hasLimitOffset() {
        return $this->limit && $this->offset;
    }
}
