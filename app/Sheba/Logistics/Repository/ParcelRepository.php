<?php namespace Sheba\Logistics\Repository;

class ParcelRepository extends BaseRepository
{
    public function getAll()
    {
        $result = $this->client->get('parcels');
        return !empty($result) ? $result['parcels'] : $result;
    }

    public function findBySlug($slug)
    {
        $result = $this->client->get("parcels/$slug");
        return !empty($result) ? $result['parcel'] : $result;
    }
}