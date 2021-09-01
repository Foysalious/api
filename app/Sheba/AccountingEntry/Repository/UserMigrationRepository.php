<?php

namespace Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\Dal\AccountingMigratedUser\EloquentImplementation;

class UserMigrationRepository
{
    /** @var EloquentImplementation  */
    private $repo;

    public function __construct(EloquentImplementation $repo)
    {
        $this->repo = $repo;
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function show($userId, $userType = UserType::PARTNER)
    {
        return $this->repo->where('user_id', $userId)->where('user_type', $userType)->first();
    }

    public function update(array $data, $userId, $userType = UserType::PARTNER)
    {
        $user = $this->show($userId, $userType);
        return $user->update(
            [
                'status' => $data['status']
            ]
        );
    }
}