<?php namespace Sheba\Business\Role;

use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BusinessRoleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class Creator
{
    use ModificationFields;

    /** BusinessRoleRepositoryInterface $businessRoleRepository */
    private $businessRoleRepository;
    /** @var Requester $requester */
    private $requester;

    /**
     * Creator constructor.
     * @param BusinessRoleRepositoryInterface $business_role_repository
     */
    public function __construct(BusinessRoleRepositoryInterface $business_role_repository)
    {
        $this->businessRoleRepository = $business_role_repository;
    }

    /**
     * @param Requester $requester
     * @return $this
     */
    public function setRequester(Requester $requester)
    {
        $this->requester = $requester;
        return $this;
    }

    /**
     * @return Model
     */
    public function create()
    {
        $data = [
            'business_department_id' => $this->requester->getDepartment(),
            'name' => $this->requester->getName(),
            'is_published' => $this->requester->getIsPublished()
        ];
        return $this->businessRoleRepository->create($this->withCreateModificationField($data));
    }
}
