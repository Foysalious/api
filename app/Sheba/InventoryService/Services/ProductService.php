<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;


class ProductService
{
    /**
     * @param InventoryServerClient $client
     */

    protected $productId;
    protected $partnerId;
    protected $categoryId;
    protected $name;
    protected $description;
    protected $warranty;
    protected $warrantyUnit;
    protected $vatPercentage;
    protected $unitId;
    protected $images;
    protected $wholesalePrice;
    protected $cost;
    protected $price;
    protected $stock;
    protected $channelId;
    protected $discountAmount;
    protected $discountEndDate;

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $productId
     * @return ProductService
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @param mixed $partnerId
     * @return ProductService
     */
    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param mixed $categoryId
     * @return ProductService
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @param mixed $name
     * @return ProductService
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $description
     * @return ProductService
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param mixed $warranty
     * @return ProductService
     */
    public function setWarranty($warranty)
    {
        $this->warranty = $warranty;
        return $this;
    }

    /**
     * @param mixed $warrantyUnit
     * @return ProductService
     */
    public function setWarrantyUnit($warrantyUnit)
    {
        $this->warrantyUnit = $warrantyUnit;
        return $this;
    }

    /**
     * @param mixed $vatPercentage
     * @return ProductService
     */
    public function setVatPercentage($vatPercentage)
    {
        $this->vatPercentage = $vatPercentage;
        return $this;
    }

    /**
     * @param mixed $unitId
     * @return ProductService
     */
    public function setUnitId($unitId)
    {
        $this->unitId = $unitId;
        return $this;
    }

    /**
     * @param mixed $images
     * @return ProductService
     */
    public function setImages($images)
    {
        $this->images = $images;
        return $this;
    }

    /**
     * @param mixed $wholesalePrice
     * @return ProductService
     */
    public function setWholesalePrice($wholesalePrice)
    {
        $this->wholesalePrice = $wholesalePrice;
        return $this;
    }

    /**
     * @param mixed $cost
     * @return ProductService
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @param mixed $price
     * @return ProductService
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @param mixed $stock
     * @return ProductService
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
        return $this;
    }

    /**
     * @param mixed $channelId
     * @return ProductService
     */
    public function setChannelId($channelId)
    {
        $this->channelId = $channelId;
        return $this;
    }

    public function setDisCountAmount($discountAmount)
    {
        $this->discountAmount  = $discountAmount;
        return $this;
    }

    public function setDiscountEndDate($discount_end_date)
    {
        $this->discountEndDate = $discount_end_date;
        return $this;
    }

    public function getAllProducts($partner_id)
    {
        $url = 'api/v1/partners/' . $partner_id . '/products';
        return $this->client->get($url);
    }

    public function getDetails()
    {
        $url = 'api/v1/partners/' . $this->partnerId . '/products/' . $this->productId;
        return $this->client->get($url);
    }

    private function makeCreateData()
    {
        return [
            'partner_id' => $this->partnerId,
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'description' => $this->description,
            'warranty' => $this->warranty ?: 0,
            'warranty_unit' => $this->warrantyUnit ?: 'day',
            'vat_percentage' => $this->vatPercentage ?: 0,
            'unit_id' => $this->unitId,
            'images' => $this->images,
            'wholesale_price' => $this->wholesalePrice,
            'cost' => $this->cost,
            'price' => $this->price,
            'stock' => $this->stock,
            'channelId' => $this->channelId,
            'discount_amount' => $this->discountAmount,
            'discount_end_date' => $this->discountEndDate,
        ];
    }

    private function makeUpdateData()
    {
        $data = [];
        if (isset($this->categoryId)) $data['category_id'] = $this->categoryId;
        if (isset($this->name)) $data['name'] = $this->name;
        if (isset($this->description)) $data['description'] = $this->description;
        if (isset($this->warranty)) $data['warranty'] = $this->warranty;
        if (isset($this->warrantyUnit)) $data['warranty_unit'] = $this->warrantyUnit;
        if (isset($this->vatPercentage)) $data['vat_percentage'] = $this->vatPercentage;
        if (isset($this->unitId)) $data['unit_id'] = $this->unitId;
        return $data;
    }

    public function store()
    {
        $data = $this->makeCreateData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/products', $data);
    }

    public function update()
    {
        $data = $this->makeCreateData();
        return $this->client->put('api/v1/partners/'.$this->partnerId.'/products/'.$this->productId, $data);
    }

    public function delete()
    {
        return $this->client->delete('api/v1/partners/'.$this->partnerId.'/products/'.$this->productId);
    }

}