<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;
use Illuminate\Support\Facades\File;

class CategoryService
{
    public $partnerId;
    public $modifier;
    public $categoryName;
    public $categoryId;
    public $client;
    protected $thumb;

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

    /**
     * @param mixed $thumb
     * @return CategoryService
     */
    public function setThumb($thumb)
    {
        $this->thumb = $thumb;
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
        return [
        ['name' => 'name', 'contents' => $this->categoryName],
        ['name' => 'modifier', 'contents' => $this->modifier],
        ['name' => 'thumb', 'contents' => $this->thumb ? File::get($this->thumb->getRealPath()) : null, 'filename' => $this->thumb ? $this->thumb->getClientOriginalName() : '']
        ];

    }

    public function makeUpdateData()
    {
        $data = [];
        $data['name'] =  $this->categoryName;
        $data['modifier']  = $this->modifier;
        return $data;

    }

    public function store()
    {
        $data = $this->makeStoreData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/categories', $data, true);
    }

    public function update()
    {
        $data = $this->makeUpdateData();
        return $this->client->put('api/v1/partners/'.$this->partnerId.'/categories/'.$this->categoryId, $data);
    }

    public function delete()
    {
        $data = $this->makeUpdateData();
        return $this->client->delete('api/v1/partners/'.$this->partnerId.'/categories/'.$this->categoryId);
    }

    public function getallcategory($partner_id)
    {
        $url = 'api/v1/partners/'.$partner_id.'/category-tree';

        return $this->client->get($url);
    }

}