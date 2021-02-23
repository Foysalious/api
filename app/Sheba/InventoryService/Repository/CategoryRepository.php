<?php namespace App\Sheba\InventoryService\Repository;


class CategoryRepository extends BaseRepository
{
    public function getAllMasterCategories($partner_id)
    {
        try {
            $url = 'api/v1/partners/'.$partner_id.'/categories';
            return $this->client->get($url);
        } catch (\Exception $e) {
            if ($e->getCode() != 403) throw $e;
        }

    }

}