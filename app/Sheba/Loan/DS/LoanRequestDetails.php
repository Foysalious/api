<?php


namespace Sheba\Loan\DS;


use Illuminate\Contracts\Support\Arrayable;

class LoanRequestDetails implements Arrayable
{
    protected $documents, $finance_info, $business_info, $personal_info, $nominee_granter_info;
    private                                                              $data;

    public function __construct($details)
    {
        $this->data = json_decode($details, true);
    }

    public function __get($name)
    {
        if (isset($this->$name)) return $this->$name;
        else return null;
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'documents'            => $this->getDocuments(),
            'finance_info'         => $this->getFinanceInfo(),
            'business_info'        => $this->getBusinessInfo(),
            'personal_info'        => $this->getPersonalInfo(),
            'nominee_granter_info' => $this->getNomineeGranterInfo()
        ];
    }

    private function getDocuments()
    {

    }

    private function getFinanceInfo()
    {
    }

    private function getBusinessInfo()
    {
    }

    private function getPersonalInfo()
    {
    }

    private function getNomineeGranterInfo()
    {
    }
}
