<?php namespace Sheba\Loan\DS;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionException;

class SanctionLetterInfo implements Arrayable
{
    private $loanDetails;

    public function __construct(LoanRequestDetails $details)
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
        return (new SanctionLetter(isset($data['sanction_letter_info']) ? (array)$data['sanction_letter_info'] : []))->toArray();
    }
}
