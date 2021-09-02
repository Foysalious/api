<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;


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
    protected $productDetails;
    protected $offset;

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

    public function setProductDetails($product_details)
    {
        $this->productDetails = $product_details;
        return $this;
    }

    public function getAllProducts($partnerId)
    {
        $url = 'api/v1/partners/' . $partnerId . '/products?';
        if (isset($this->limit)) $url .= 'offset='.$this->offset.'&limit='.$this->limit.'&';
        if (isset($this->category_ids)) $url .= 'category_ids='.$this->category_ids.'&';
        if (isset($this->sub_category_ids)) $url .= 'sub_category_ids='.$this->sub_category_ids.'&';
        if (isset($this->updated_after)) $url .= 'updated_after='.$this->updated_after . '&';
        if (isset($this->is_published_for_webstore)) $url .= 'is_published_for_webstore=' . $this->is_published_for_webstore;
        return $this->client->get($url);
    }

    public function getDetails()
    {
        $url = 'api/v1/partners/' . $this->partnerId . '/products/' . $this->productId;
        return $this->client->get($url);
    }

    private function makeCreateData()
    {
        $data = [
            ['name' => 'partner_id', 'contents' => $this->partnerId],
            ['name' => 'category_id', 'contents' => $this->categoryId],
            ['name' => 'name','contents' => $this->name],
            ['name' => 'description','contents' => $this->description],
            ['name' => 'warranty','contents' => $this->warranty ?: 0],
            ['name' => 'warranty_unit','contents' => $this->warrantyUnit ?: 'day'],
            ['name' => 'vat_percentage','contents' => $this->vatPercentage ?: 0],
            ['name' => 'unit_id', 'contents' => $this->unitId],
            ['name' => 'discount_amount', 'contents' => $this->discountAmount],
            ['name' => 'discount_end_date', 'contents' => $this->discountEndDate],
            ['name' => 'product_details', 'contents' => $this->productDetails],
        ];
        if (isset($this->images)) $data = array_merge($data, $this->makeImagesData());
        return $data;
    }

    private function makeUpdateData()
    {
        $data = [];
        if (isset($this->categoryId)) array_push($data, ['name' => 'category_id', 'contents' => $this->categoryId]);
        if (isset($this->name)) array_push($data, ['name' => 'name','contents' => $this->name]);
        if (isset($this->description)) array_push($data, ['name' => 'description','contents' => $this->description]);
        if (isset($this->warranty)) array_push($data, ['name' => 'warranty','contents' => $this->warranty ?: 0]);
        if (isset($this->warrantyUnit)) array_push($data, ['name' => 'warranty_unit','contents' => $this->warrantyUnit ?: 'day']);
        if (isset($this->vatPercentage))  array_push($data, ['name' => 'vat_percentage','contents' => $this->vatPercentage ?: 0]);
        if (isset($this->unitId))  array_push($data, ['name' => 'unit_id', 'contents' => $this->unitId]);
        if (isset($this->discountAmount))array_push($data, ['name' => 'discount_amount', 'contents' => $this->discountAmount]);
        if (isset($this->discountEndDate))array_push($data, ['name' => 'discount_end_date', 'contents' => $this->discountEndDate]);
        if (isset($this->productDetails))  array_push($data, ['name' => 'product_details', 'contents' => $this->productDetails]);
        if (isset($this->images)) $data = array_merge($data, $this->makeImagesData());
        return $data;
    }

    private function makeImagesData()
    {
        $images = [];
        foreach ($this->images as $key => $image)
        {
            array_push($images, ['name' => 'images['.$key.']', 'contents' => File::get($image->getRealPath()), 'filename' => $image->getClientOriginalName()]);
        }
        return $images;
    }

    public function store()
    {
        $data = $this->makeCreateData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/products', $data, true);
    }

    public function update()
    {
        $data = $this->makeUpdateData();
        return $this->client->put('api/v1/partners/'.$this->partnerId.'/products/'.$this->productId, $data, true);
    }

    public function delete()
    {
        return $this->client->delete('api/v1/partners/'.$this->partnerId.'/products/'.$this->productId);
    }

    public function getLogs()
    {
        return $this->client->get('api/v1/partners/'. $this->partnerId . '/products/' .  $this->productId . '/logs');
    }

}