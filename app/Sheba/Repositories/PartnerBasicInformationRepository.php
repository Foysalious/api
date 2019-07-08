<?php namespace Sheba\Repositories;

use App\Models\PartnerBasicInformation;

class PartnerBasicInformationRepository extends BaseRepository
{
    public function __construct(PartnerBasicInformation $basic_information)
    {
        parent::__construct();
        $this->setModel($basic_information);
    }
}