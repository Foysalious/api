<?php
namespace App\Sheba\Loan;
use Sheba\Helpers\ConstGetter;

class LoanDocumentTypes
{
    use ConstGetter;
    const PROFILE = 'profile';
    const PROVE_OF_PHOTOGRAPH = 'proof_of_photograph';
}