<?php namespace App\Sheba\InventoryService\Repository;


class CategoryRepository extends BaseRepository
{

    public $partnerId;
    public $modifier;
    public $categoryName;

    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

    public function setPartner($partner_id)
    {
        $this->partnerId = $partner_id;
        return $this;
    }

    public function setCategoryName($category_name)
    {
        $this->categoryName = $category_name;
        return $this;
    }


    public function getAllMasterCategories($partner_id)
    {
        try {
            $url = 'api/v1/partners/'.$partner_id.'/categories';
            return $this->client->get($url);
        } catch (\Exception $e) {
            if ($e->getCode() != 403) throw $e;
        }

    }
    public function makeData()
    {
        $data = [];
        $data['name'] = $this->categoryName;
        $data['modifier']  = $this->modifier;
        $data['partner_id'] = $this->partnerId;
        return $data;

    }

    public function store()
    {
        try{
            $data = $this->makeData();
            return $this->client->post('api/v1/partners/'.$this->partnerId.'/categories', $data);
        }catch(\Exception $e) {
            if ($e->getCode() != 403) throw $e;
        }
    }

}