<?php namespace Sheba\Repositories;

use App\Models\ProfileMobileBankInformation;
use Sheba\Repositories\Interfaces\ProfileMobileBankingRepositoryInterface;

class ProfileMobileBankingRepository extends BaseRepository implements ProfileMobileBankingRepositoryInterface
{
    public function __construct(ProfileMobileBankInformation $bank)
    {
        parent::__construct();
        $this->setModel($bank);
    }
}
