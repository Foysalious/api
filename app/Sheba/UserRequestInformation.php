<?php namespace App\Sheba;

use Illuminate\Http\Request;

class UserRequestInformation
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getInformationArray()
    {
        return array(
            'portal_name' => $this->request->header('portal-name') != null ? $this->request->header('portal-name') : 'customer-portal',
            'user_agent' => $this->request->header('User-Agent'),
            'ip' => $this->request->ip()
        );
    }
}