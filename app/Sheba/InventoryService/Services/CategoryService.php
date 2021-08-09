<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;
use Illuminate\Support\Facades\File;

class CategoryService
{
    public $partnerId;
    public $modifier;
    public $categoryName;
    public $categoryId;
    public $parentId;
    public $client;
    public $subCategories;
    protected $thumb;
    private $updatedAfter;

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

    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @param mixed $subCategories
     */
    public function setSubCategories($subCategories)
    {
        $this->subCategories = $subCategories;
        return $this;
    }

    /**
     * @param mixed $updatedAfter
     * @return CategoryService
     */
    public function setUpdatedAfter($updatedAfter)
    {
        $this->updatedAfter = $updatedAfter;
        return $this;
    }


    public function getAllMasterCategories($partner_id)
    {
        $url = 'api/v1/partners/'.$partner_id.'/categories?';
        if($this->updatedAfter) $url .= 'updated_after='.$this->updatedAfter;
        return $this->client->get($url);
    }

    public function makeStoreData()
    {
        $data =  [
            ['name' => 'name', 'contents' => $this->categoryName],
            ['name' => 'modifier', 'contents' => $this->modifier],
            ['name' => 'thumb', 'contents' => $this->thumb ? File::get($this->thumb->getRealPath()) : null, 'filename' => $this->thumb ? $this->thumb->getClientOriginalName() : '']
        ];
        if ($this->parentId != null) {
            $data = array_merge_recursive($data,[
                [
                    'name' => 'parent_id',
                    'contents' => $this->parentId,
                ]
            ]);
        }
        return $data;
    }

    public function makeUpdateData()
    {
        return [
            ['name' => 'name', 'contents' => $this->categoryName],
            ['name' => 'modifier', 'contents' => $this->modifier],
            ['name' => 'thumb', 'contents' => $this->thumb ? File::get($this->thumb->getRealPath()) : null, 'filename' => $this->thumb ? $this->thumb->getClientOriginalName() : '']
        ];
    }

    public function store()
    {
        $data = $this->makeStoreData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/categories', $data, true);
    }

    public function update()
    {
        $data = $this->makeUpdateData();
        return $this->client->put('api/v1/partners/'.$this->partnerId.'/categories/'.$this->categoryId, $data, true);
    }

    public function storeCategoryWithSubCategory()
    {
        $data = $this->makeStoreDataForCategoryWithSubCategory();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/category-with-sub-category', $data, true);
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

    public function makeStoreDataForCategoryWithSubCategory()
    {
        $data =  [
            ['name' => 'category_name', 'contents' => $this->categoryName],
            ['name' => 'modifier', 'contents' => $this->modifier],
            [
                'name' => 'category_thumb',
                'contents' => $this->thumb ? File::get($this->thumb->getRealPath()) : null,
                'filename' => $this->thumb ? $this->thumb->getClientOriginalName() : ''
            ],
        ];
        $sub_category = [];
        foreach ( $this->subCategories as $key=>$value) {
            $sub_category [] =  ['name' => "sub_category[$key][name]", 'contents' => $value['name']];
            $this->thumb = $value['thumb'];
            $sub_category [] = [
                'name' => "sub_category[$key][thumb]",
                'contents' => $this->thumb ? File::get($this->thumb->getRealPath()) : null,
                'filename' => $this->thumb ? $this->thumb->getClientOriginalName() : ''
            ];
        }
        return array_merge_recursive($data,$sub_category);
    }
}