<?php namespace Sheba\Reports\PartnerPayments;

use App\Models\Partner;
use Sheba\Reports\TopSheet as TopSheetData;

class TopSheet extends PartnerPayments
{
    /** @var Partner */
    private $partner;

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function get()
    {
        return (new TopSheetData($this->partner, $this->session))->calculate();
    }
}