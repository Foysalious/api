<?php namespace Sheba\Reports\Pos;


use Illuminate\Http\Request;

abstract class PosReport
{
    /**
     * @var $request Request
     */
    protected $request, $orderBy, $range, $to, $query, $order;

    protected function validateRequest()
    {
    }

    protected function prepareQueryParams()
    {
        $this->orderBy='';
    }

    protected function setRequest(Request $request)
    {
        $this->request = (array)$request;
    }
    private function hasInRequest(){

    }
}
