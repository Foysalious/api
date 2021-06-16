<?php namespace Sheba\Repositories\Business;

use App\Helper\BangladeshiMobileValidator;
use App\Models\BusinessMember;
use App\Models\Profile;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class BusinessMemberRepository extends BaseRepository implements BusinessMemberRepositoryInterface
{
    public function __construct(BusinessMember $business_member)
    {
        parent::__construct();
        $this->setModel($business_member);
    }

    /**
     * Checking existing profile mobile.
     *
     * @param $mobile
     * @return Profile
     */
    public function checkExistingMobile($mobile)
    {
        $mobile = $mobile ? formatMobileAux($mobile) : null;
        $mobile = BangladeshiMobileValidator::validate($mobile) ? $mobile : null;
        if (!$mobile) return null;
        return BusinessMember::where('mobile', $mobile)->first();
    }
}