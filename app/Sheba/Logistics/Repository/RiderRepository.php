<?php namespace Sheba\Logistics\Repository;

class RiderRepository extends BaseRepository
{
    public function getByProfileId($profile_id)
    {
        $result = $this->client->get('riders/get-by-profile/' . $profile_id);
        return !empty($result) ? $result['rider'] : $result;
    }
}