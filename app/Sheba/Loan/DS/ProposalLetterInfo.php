<?php namespace Sheba\Loan\DS;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionException;

class ProposalLetterInfo implements Arrayable
{
    private $loanDetails;

    public function __construct(LoanRequestDetails $details = null)
    {
        $this->loanDetails = $details;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function toArray()
    {
        return $this->loanDetails ? $this->dataFromLoanRequest() : [];
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function dataFromLoanRequest()
    {
        $data = $this->loanDetails->getData();
        return (new ProposalLetter(isset($data['proposal_info']) ? (array)$data['proposal_info'] : []))->toArray();
    }
}
