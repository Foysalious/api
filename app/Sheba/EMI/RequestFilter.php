<?php namespace Sheba\EMI;


use Illuminate\Http\Request;

class RequestFilter {
    private $request, $limit, $offset, $recent;

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

    private function __init() {
        list($limit, $offset) = calculatePagination($this->request);
        $this->setLimit($limit);
        $this->setOffset($offset);
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
}
