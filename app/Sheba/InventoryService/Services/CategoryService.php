<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class CategoryService
{
    public $partnerId;
    public $modifier;
    public $categoryName;
    public $categoryId;
    public $client;

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

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

    public function setCategoryId($category_id)
    {
        $this->categoryId = $category_id;
        return $this;
    }

    public function getAllMasterCategories($partner_id)
    {
        $url = 'api/v1/partners/'.$partner_id.'/categories';
        return $this->client->get($url);
    }

    public function makeStoreData()
    {
        $data = [];
        $data['name'] = $this->categoryName;
        $data['modifier']  = $this->modifier;
        return $data;

    }

    public function makeUpdateData()
    {
        $data = [];
        $data['name'] = $this->categoryName;
        $data['modifier']  = $this->modifier;
        return $data;

    }

    public function store()
    {
        $data = $this->makeStoreData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/categories', $data);
    }

    public function update()
    {
        $data = $this->makeUpdateData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/categories/'.$this->categoryId, $data);
    }

}