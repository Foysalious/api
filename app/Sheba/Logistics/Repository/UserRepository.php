<?php namespace Sheba\Logistics\Repository;

class UserRepository  extends BaseRepository
{
    public function getByProfileId($profile_id)
    {
        $result = $this->client->get('users/get-by-profile/' . $profile_id);
        return !empty($result) ? $result['user'] : $result;
    }
}