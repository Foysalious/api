<?php namespace Sheba\Logistics;

trait ValidatesApiRequest
{
    /**
     * @return array|bool
     */
    public function hasErrorAccessingApiFromLogistic()
    {
        if($this->request->access_token != config('logistics.access_token_for_api'))
            return ['code' => 401, 'msg' => "Unauthorized"];
        return false;
    }
}