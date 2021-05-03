<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class CategoryProductService
{
    private $category_id;
    private $master_category_id;
    private $updated_after;
    private $offset;
    private $limit;

    /**
     * @param mixed $category_id
     * @return CategoryProductService
     */
    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;
        return $this;
    }

    /**
     * @param mixed $master_category_id
     * @return CategoryProductService
     */
    public function setMasterCategoryId($master_category_id)
    {
        $this->master_category_id = $master_category_id;
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

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    public function getProducts($partnerId)
    {
        $url = 'api/v1/partners/' . $partnerId . '/category-products?';
        if (isset($this->limit)) $url .= 'offset='.$this->offset.'&limit='.$this->limit.'&';
        if (isset($this->master_category_id)) $url .= 'master_category_id='.$this->master_category_id.'&';
        if (isset($this->category_id)) $url .= 'category_id='.$this->category_id.'&';
        if (isset($this->updated_after)) $url .= 'updated_after='.$this->updated_after;
        return $this->client->get($url);
    }

}