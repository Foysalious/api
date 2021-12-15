<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;
use Illuminate\Support\Facades\File;

class CategoryService
{
    public $partnerId;
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
        $url = 'api/v1/partners/' . $partner_id . '/categories?';
        if ($this->updatedAfter) $url .= 'updated_after=' . $this->updatedAfter;
        return $this->client->get($url);
    }

    public function makeSubCategoryStoreData()
    {
        $data = [
            ['name' => 'name', 'contents' => $this->categoryName],
        ];
        if ($this->thumb) {
            $data[] = ['name' => 'thumb', 'contents' => $this->thumb ? File::get($this->thumb->getRealPath()) : null, 'filename' => $this->thumb ? $this->thumb->getClientOriginalName() : ''];
        }
        if ($this->parentId != null) {
            $data [] = [
                'name' => 'parent_id',
                'contents' => $this->parentId,
            ];
        }
        return $data;
    }

    public function makeUpdateData()
    {
        $data [] =  ['name' => 'name', 'contents' => $this->categoryName];
        if($this->thumb && is_file($this->thumb)) {
            $data [] = [
                'name' => 'thumb', 'contents' => File::get($this->thumb->getRealPath()),
                'filename' => $this->thumb->getClientOriginalName(),
            ];
        }
        return $data;
    }

    public function createSubCategory()
    {
        $data = $this->makeSubCategoryStoreData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/categories/sub-category', $data, true);
    }

    public function update()
    {
        $data = $this->makeUpdateData();
        return $this->client->put('api/v1/partners/' . $this->partnerId . '/categories/' . $this->categoryId, $data, true);
    }

    public function storeCategoryWithSubCategory()
    {
        $data = $this->makeStoreDataForCategoryWithSubCategory();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/categories', $data, true);
    }

    public function delete()
    {
        return $this->client->delete('api/v1/partners/' . $this->partnerId . '/categories/' . $this->categoryId);
    }

    public function getallcategory($partner_id)
    {
        $url = 'api/v1/partners/' . $partner_id . '/category-tree?';
        if ($this->updatedAfter) $url .= 'updated_after=' . $this->updatedAfter;

        return $this->client->get($url);
    }

    public function makeStoreDataForCategoryWithSubCategory(): array
    {
        $data [] =  ['name' => 'category_name', 'contents' => $this->categoryName];
        if($this->thumb && is_file($this->thumb)) {
            $data [] = [
                'name' => 'category_thumb',
                'contents' => File::get($this->thumb->getRealPath()),
                'filename' => $this->thumb->getClientOriginalName()
            ];
        }
        if (!$this->subCategories) return $data;
        $sub_category = [];
        foreach ( $this->subCategories as $key=>$value) {
            $sub_category [] =  ['name' => "sub_category[$key][name]", 'contents' => $value['name']];
            if(isset($value['thumb']) && is_file($value['thumb'])) {
                $this->thumb = $value['thumb'];
                $sub_category [] = [
                    'name' => "sub_category[$key][thumb]",
                    'contents' => File::get($this->thumb->getRealPath()),
                    'filename' => $this->thumb->getClientOriginalName(),
                ];
            }
        }
        return array_merge_recursive($data, $sub_category);
    }

    public function getCategoryDetail()
    {
        return $this->client->get('api/v1/partners/' . $this->partnerId . '/categories/' . $this->categoryId);
    }

    public function getPartnerWiseCategoryList($partner)
    {
        return $this->client->get('api/v1/webstore/partners/' . $partner . '/categories');
    }
}
