<?php


namespace Sheba\Loan;


use App\Models\PartnerBankLoan;
use Sheba\Repositories\BaseRepository;

class LoanRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->setModel(app(PartnerBankLoan::class));
    }

    public function findByBankId($bank_id)
    {
        return $this->model->where('bank_id', $bank_id)->get();
    }
}
