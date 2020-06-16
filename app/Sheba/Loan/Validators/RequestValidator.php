<?php


namespace Sheba\Loan\Validators;


use App\Models\BankUser;
use App\Models\PartnerBankLoan;
use App\Models\User;
use Sheba\Dal\RetailerMembers\RetailerMember;
use Sheba\Loan\Exceptions\NotAllowedToAccess;

class RequestValidator
{
    private $user;
    private $loan;

    public function __construct(PartnerBankLoan $partner_bank_loan)
    {
        $this->user = request()->user;
        $this->loan = $partner_bank_loan;
    }

    /**
     * @throws NotAllowedToAccess
     */
    public function validate()
    {
        $user = $this->user;
        if (!empty($user) && (!($user instanceof User) && ($user instanceof BankUser && $user->bank->id != $this->loan->bank_id)) && !($user instanceof RetailerMember)) {
            throw new NotAllowedToAccess();
        }
    }
}
