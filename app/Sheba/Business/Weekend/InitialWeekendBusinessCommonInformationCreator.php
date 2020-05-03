<?php namespace App\Sheba\Business\Weekend;

use App\Sheba\Business\BusinessCommonInformation;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\ModificationFields;

class InitialWeekendBusinessCommonInformationCreator extends BusinessCommonInformation
{
    use ModificationFields;
    /** @var BusinessWeekendRepoInterface $weekendRepository */
    private $weekendRepository;

    public function __construct(BusinessWeekendRepoInterface $weekend_repo)
    {
        $this->weekendRepository = $weekend_repo;
    }

    public function create()
    {
        $this->weekendRepository->create($this->withCreateModificationField([
            'business_id' => $this->business->id,
            'weekday_name' => 'friday',
        ]));
        $this->weekendRepository->create($this->withCreateModificationField([
            'business_id' => $this->business->id,
            'weekday_name' => 'saturday',
        ]));
    }
}