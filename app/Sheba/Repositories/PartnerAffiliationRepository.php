<?php namespace Sheba\Repositories;

use App\Models\PartnerAffiliation;

class PartnerAffiliationRepository extends BaseRepository
{
    public function update(PartnerAffiliation $affiliation, $data)
    {
        $affiliation->update($this->withUpdateModificationField($data));
    }
}