<?php


namespace Sheba\NeoBanking;

use App\Models\Partner;
use Sheba\NeoBanking\Banks\BankFactory;
use Sheba\NeoBanking\Repositories\NeoBankRepository;
use Sheba\NeoBanking\Statics\BankStatics;

class Home
{
    /** @var NeoBankRepository */
    private $bankRepo;
    /** @var Partner $partner */
    private $partner;

    /**
     * @param Partner $partner
     * @return Home
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }
    public function __construct()
    {
        $this->bankRepo = new NeoBankRepository();
    }

    /**
     * @throws Exceptions\InvalidBankCode
     */
    public function get()
    {
        $data['banks'] = [];
        foreach ($this->bankRepo->getAll() as $bank) {
            $data['banks'][] = (new BankFactory())->setPartner($this->partner)->setBank($bank)->get()->homeInfo();
        }
        $data['account_details_view_link'] = BankStatics::AccountDetailsURL();
        return $data;
    }

}
