<?php namespace App\Sheba\Business\Weekend;

use App\Sheba\Business\BusinessCommonInformation;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\ModificationFields;
use Sheba\Business\Weekend\CreateRequest;

class Creator
{
    use ModificationFields;
    /** @var BusinessWeekendRepoInterface $weekendRepository */
    private $weekendRepository;
    /** @var CreateRequest $weekendCreateRequest */
    private $weekendCreateRequest;

    /**
     * Creator constructor.
     * @param BusinessWeekendRepoInterface $weekend_repo
     */
    public function __construct(BusinessWeekendRepoInterface $weekend_repo)
    {
        $this->weekendRepository = $weekend_repo;
    }

    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setWeekendCreateRequest(CreateRequest $create_request)
    {
        $this->weekendCreateRequest = $create_request;
        return $this;
    }

    public function create()
    {
        $this->weekendRepository->create($this->withCreateModificationField([
            'business_id' => $this->weekendCreateRequest->getBusiness()->id,
            'weekday_name' => $this->weekendCreateRequest->getWeekday(),
        ]));
    }
}