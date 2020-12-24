<?php namespace Sheba\Repositories;

use App\Models\ProfileBankInformation;
use Sheba\Repositories\Interfaces\ProfileBankingRepositoryInterface;

class ProfileBankingRepository extends BaseRepository implements ProfileBankingRepositoryInterface
{
    public function __construct(ProfileBankInformation $bank)
    {
        parent::__construct();
        $this->setModel($bank);
    }
}
