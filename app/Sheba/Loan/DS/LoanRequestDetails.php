<?php


namespace Sheba\Loan\DS;


use App\Models\PartnerBankLoan;
use Illuminate\Contracts\Support\Arrayable;
use Sheba\ModificationFields;

class LoanRequestDetails implements Arrayable
{
    use ModificationFields;
    protected $documents;
    protected $finance_info;
    protected $business_info;
    protected $personal_info;
    protected $nominee_granter_info;
    private   $partner;
    private   $resource;
    private $data;

    public function __construct(PartnerBankLoan $request)
    {
        $this->data = json_decode($request->final_information_for_loan, true);
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $resource
     * @return LoanRequestDetails
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param mixed $partner
     * @return LoanRequestDetails
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
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

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getFinanceInfo()
    {
        return (new FinanceInfo($this->partner, $this->resource))->toArray();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getBusinessInfo()
    {
        return (new BusinessInfo($this->partner, $this->resource))->toArray();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getPersonalInfo()
    {
        return (new PersonalInfo($this->partner,$this->resource))->toArray();
    }

    private function getNomineeGranterInfo()
    {
    }
}
