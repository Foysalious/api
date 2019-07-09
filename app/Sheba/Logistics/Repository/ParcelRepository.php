<?php namespace Sheba\Logistics\Repository;

use Sheba\Logistics\Exceptions\LogisticServerError;

class ParcelRepository extends BaseRepository
{
    /**
     * @return mixed
     * @throws LogisticServerError
     */
    public function getAll()
    {
        $result = $this->client->get('parcels');
        return !empty($result) ? $result['parcels'] : $result;
    }

    /**
     * @param $slug
     * @return mixed
     * @throws LogisticServerError
     */
    public function findBySlug($slug)
    {
        $result = $this->client->get("parcels/$slug");
        return !empty($result) ? $result['parcel'] : $result;
    }
}