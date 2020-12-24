<?php

namespace Sheba\Loan\DS;

use App\Sheba\Loan\DS\DocumentsForAgents;
use Illuminate\Contracts\Support\Arrayable;
use ReflectionException;
use Sheba\ModificationFields;

class LoanRequestDetails implements Arrayable
{
    use ModificationFields;
    public    $partnerLoanRequest;
    protected $documents;
    protected $finance;
    protected $business;
    protected $personal;
    protected $nominee_granter;
    private   $partner;
    private   $resource;
    private   $data;

    public function __construct(PartnerLoanRequest $request)
    {
        $this->partnerLoanRequest = $request;
        $this->setPartner($this->partnerLoanRequest->getPartner());
        $this->setData();
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData()
    {
        $this->data = json_decode($this->partnerLoanRequest->partnerBankLoan->final_information_for_loan, true);

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

    /**
     * Get the instance as an array.
     *
     * @return array
     * @throws ReflectionException
     */
    public function toArray()
    {
        return [
            'document'             => $this->getDocuments(),
            'finance'              => $this->getFinanceInfo(),
            'business'             => $this->getBusinessInfo(),
            'personal'             => $this->getPersonalInfo(),
            'nominee_granter'      => $this->getNomineeGranterInfo(),
            'proposal_info'        => $this->getProposalInfo(),
            'sanction_letter_info' => $this->sanctionLetterInfo()
        ];
    }

    /**
     * @return array|void
     */
    public function getDocuments()
    {
        return (new Documents($this->partner, $this->resource, $this))->toArray();
    }

    /**
     * @return array
     */
    public function getDocumentsForAgents()
    {
        return (new DocumentsForAgents($this->partner, $this->resource, $this))->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getFinanceInfo()
    {
        return (new FinanceInfo($this->partner, $this->resource, $this))->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getBusinessInfo()
    {
        return (new BusinessInfo($this->partner, $this->resource, $this))->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getPersonalInfo()
    {
        return (new PersonalInfo($this->partner, $this->resource, $this))->toArray();
    }

    /**
     * @return array|void
     * @throws ReflectionException
     */
    private function getNomineeGranterInfo()
    {
        return (new NomineeGranterInfo($this->partner, $this->resource, $this))->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getProposalInfo()
    {
        return (new ProposalLetterInfo($this))->toArray();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function sanctionLetterInfo()
    {
        return (new SanctionLetterInfo($this))->toArray();
    }
}
