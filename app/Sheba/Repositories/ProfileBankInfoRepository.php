<?php namespace Sheba\Repositories;

use Sheba\Repositories\Interfaces\ProfileBankInfoInterface;
use App\Models\ProfileBankInformation;

class ProfileBankInfoRepository extends BaseRepository implements ProfileBankInfoInterface
{
    public function __construct(ProfileBankInformation $profile_bank_information)
    {
        parent::__construct();
        $this->setModel($profile_bank_information);
    }
}