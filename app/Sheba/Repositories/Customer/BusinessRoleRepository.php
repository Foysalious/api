<?php namespace Sheba\Repositories\Customer;

use Sheba\Repositories\Interfaces\BusinessRoleRepositoryInterface;
use Sheba\Repositories\BaseRepository;
use App\Models\BusinessRole;

class BusinessRoleRepository extends BaseRepository implements BusinessRoleRepositoryInterface
{
    public function __construct(BusinessRole $business_role)
    {
        parent::__construct();
        $this->setModel($business_role);
    }
}