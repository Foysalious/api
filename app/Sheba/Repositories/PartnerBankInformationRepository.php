<?php namespace App\Sheba\Repositories;

use App\Models\PartnerBankInformation;
use Sheba\Repositories\BaseRepository;

class PartnerBankInformationRepository extends BaseRepository
{
    public function __construct(PartnerBankInformation $bank_information)
    {
        parent::__construct();
        $this->setModel($bank_information);
    }
}