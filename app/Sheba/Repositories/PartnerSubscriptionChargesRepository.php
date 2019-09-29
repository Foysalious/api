<?php namespace App\Sheba\Repositories;

use App\Models\PartnerSubscriptionPackageCharge;
use Sheba\Repositories\BaseRepository;

class PartnerSubscriptionChargesRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    public function forReport($partner_id = null)
    {
        if (!empty($partner_id)) {
            return PartnerSubscriptionPackageCharge::where('partner_id', $partner_id)->with('partner');
        }
        return PartnerSubscriptionPackageCharge::with('partner');
    }

    public function create(array $attr)
    {
        return PartnerSubscriptionPackageCharge::create($this->withCreateModificationField($attr));
    }
}
