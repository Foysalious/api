<?php namespace Sheba\Logistics\Repository;

use Sheba\Logistics\Exceptions\LogisticServerError;

class UserRepository  extends BaseRepository
{
    /**
     * @param $profile_id
     * @return mixed
     * @throws LogisticServerError
     */
    public function getByProfileId($profile_id)
    {
        $result = $this->client->get('users/get-by-profile/' . $profile_id);
        return !empty($result) ? $result['user'] : $result;
    }
}