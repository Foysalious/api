<?php namespace Sheba\Business\Role;

use App\Models\BusinessRole;
use Sheba\Repositories\Interfaces\BusinessRoleRepositoryInterface;

class Updater
{

    /** BusinessRoleRepositoryInterface $businessRoleRepository */
    private $businessRoleRepository;
    /** @var Requester $requester */
    private $requester;
    /** @var BusinessRole $businessRole */
    private $businessRole;

    /**
     * Updater constructor.
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
     * @param BusinessRole $business_role
     * @return $this
     */
    public function setRole(BusinessRole $business_role)
    {
        $this->businessRole = $business_role;
        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update()
    {
        $data = [
            'business_department_id' => $this->requester->getDepartment(),
            'name' => $this->requester->getName(),
            'is_published' => $this->requester->getIsPublished(),
        ];
        return $this->businessRoleRepository->update($this->businessRole, $data);
    }
}