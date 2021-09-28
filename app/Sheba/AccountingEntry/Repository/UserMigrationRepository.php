<?php

namespace Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Repository\BaseRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\AccountingMigratedUser\EloquentImplementation;
use Sheba\Dal\AccountingMigratedUser\UserStatus;

class UserMigrationRepository extends BaseRepository
{
    /** @var EloquentImplementation */
    private $repo;
    /** @var string */
    private $api = 'api/user-migration/';

    public function __construct(EloquentImplementation $repo, AccountingEntryClient $client)
    {
        parent::__construct($client);
        $this->repo = $repo;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    /**
     * @param $userId
     * @param string $userType
     * @return mixed
     */
    public function show($userId, $userType = UserType::PARTNER)
    {
        return $this->repo->where('user_id', $userId)->where('user_type', $userType)->first();
    }

    /**
     * @param array $data
     * @param $userId
     * @param string $userType
     * @return mixed
     * @throws Exception
     */
    public function update(array $data, $userId, $userType = UserType::PARTNER)
    {
        $user = $this->show($userId, $userType);
        if (!$user) {
            throw new Exception('User not Found!', 404);
        }
        if ($user->status == UserStatus::PENDING) {
            $data['status'] = 'upgrading';
        }
        if ($user->status == UserStatus::UPGRADING) {
            $data['status'] = 'upgraded';
        }
        if (($user->status == UserStatus::UPGRADED)) {
            throw new Exception('Sorry! Already migrated', 404);
        }
        return $user->update(
            [
                'status' => $data['status']
            ]
        );
    }

    /**
     * @param array $data
     * @param $userId
     * @param string $userType
     * @return mixed
     * @throws Exception
     */
    public function updateStatus(array $data, $userId, $userType = UserType::PARTNER)
    {
        $user = $this->update($data, $userId, $userType);
        //TODO: commented for Razoan
//        $this->migrateInAccounting($userId);
        return $user;
    }

    /**
     * @param $userId
     * @param string $userType
     * @return mixed
     * @throws AccountingEntryServerError
     */
    private function migrateInAccounting($userId, $userType = UserType::PARTNER)
    {
        try {
            $res = $this->client->setUserType($userType)->setUserId($userId)->get($this->api . $userId);
            Log::info(["successful", $res]);
            return $res;
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $userId
     * @param string $userType
     * @return null
     */
    public function userStatus($userId, $userType = UserType::PARTNER)
    {
        return 'upgraded';
//        $data = $this->show($userId, $userType);
//        if ($data && $data->status) {
//            return $data->status;
//        }
//        return null;
    }
}