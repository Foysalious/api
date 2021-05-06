<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class CategoryProductService
{
    private $category_ids;
    private $master_category_ids;
    private $updated_after;
    private $is_published_for_webstore;
    private $offset;
    private $limit;

    /**
     * @param mixed $category_ids
     * @return CategoryProductService
     */
    public function setCategoryIds($category_ids)
    {
        $this->category_ids = $category_ids;
        return $this;
    }

    /**
     * @param mixed $master_category_ids
     * @return CategoryProductService
     */
    public function setMasterCategoryIds($master_category_ids)
    {
        $this->master_category_ids = $master_category_ids;
        return $this;
    }

    /**
     * @param mixed $updated_after
     * @return CategoryProductService
     */
    public function setUpdatedAfter($updated_after)
    {
        $this->updated_after = $updated_after;
        return $this;
    }

    /**
     * @param mixed $offset
     * @return CategoryProductService
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param mixed $limit
     * @return CategoryProductService
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param mixed $is_published_for_webstore
     */
    public function setIsPublishedForWebstore($is_published_for_webstore)
    {
        $this->is_published_for_webstore = $is_published_for_webstore;
        return $this;
    }

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    public function getProducts($partnerId)
    {
        $url = 'api/v1/partners/' . $partnerId . '/category-products?';
        if (isset($this->limit)) $url .= 'offset='.$this->offset.'&limit='.$this->limit.'&';
        if (isset($this->master_category_ids)) $url .= 'master_category_ids='.$this->master_category_ids.'&';
        if (isset($this->category_ids)) $url .= 'category_ids='.$this->category_ids.'&';
        if (isset($this->updated_after)) $url .= 'updated_after='.$this->updated_after . '&';
        if (isset($this->is_published_for_webstore)) $url .= 'is_published_for_webstore=' . $this->is_published_for_webstore;
        return $this->client->get($url);
    }

}