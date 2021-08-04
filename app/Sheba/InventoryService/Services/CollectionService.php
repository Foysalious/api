<?php


namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;

class CollectionService
{
    protected $name;
    protected $description;
    protected $partner_id, $is_published, $thumb, $banner, $app_thumb, $app_banner, $sharding_id;
    protected $collection_id, $products;
    protected $offset,$limit;

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $collection_id
     * @return CollectionService
     */
    public function setCollectionId($collection_id)
    {
        $this->collection_id = $collection_id;
        return $this;
    }

    /**
     * @param mixed $name
     * @return CollectionService
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $description
     * @return CollectionService
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param mixed $partner_id
     * @return CollectionService
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * @param mixed $is_published
     * @return CollectionService
     */
    public function setIsPublished($is_published)
    {
        $this->is_published = $is_published;
        return $this;
    }

    /**
     * @param mixed $thumb
     * @return CollectionService
     */
    public function setThumb($thumb)
    {
        $this->thumb = $thumb;
        return $this;
    }

    /**
     * @param mixed $banner
     * @return CollectionService
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;
        return $this;
    }

    /**
     * @param mixed $app_thumb
     * @return CollectionService
     */
    public function setAppThumb($app_thumb)
    {
        $this->app_thumb = $app_thumb;
        return $this;
    }

    /**
     * @param mixed $app_banner
     * @return CollectionService
     */
    public function setAppBanner($app_banner)
    {
        $this->app_banner = $app_banner;
        return $this;
    }

    /**
     * @param mixed $sharding_id
     * @return CollectionService
     */
    public function setShardingId($sharding_id)
    {
        $this->sharding_id = $sharding_id;
        return $this;
    }

    /**
     * @param mixed $products
     * @return CollectionService
     */
    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }

    /**
     * @param mixed $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function getAllCollection()
    {
        return $this->client->get('api/v1/partners/' . $this->partner_id . '/collections?'. 'offset='. $this->offset . '&limit='. $this->limit  );
    }

    public function store()
    {
        $data = $this->makeCreateData();
        return $this->client->post('api/v1/partners/' . $this->partner_id . '/collections', $data, true);
    }

    public function update()
    {
        $data = $this->makeCreateData();
        return $this->client->put('api/v1/partners/' . $this->partner_id . '/collections/' . $this->collection_id, $data, true);
    }

    public function getDetails()
    {
        return $this->client->get('api/v1/partners/' . $this->partner_id . '/collections/'. $this->collection_id);
    }

    private function makeCreateData()
    {
        return [
            ['name' => 'name', 'contents' => $this->name],
            ['name' => 'description', 'contents' => $this->description],
            ['name' => 'partner_id', 'contents' => $this->partner_id],
            ['name' => 'is_published', 'contents' => $this->is_published],
            ['name' => 'thumb', 'contents' => $this->thumb ? File::get($this->thumb->getRealPath()) : null, 'filename' => $this->thumb ? $this->thumb->getClientOriginalName() : ''],
            ['name' => 'banner', 'contents' => $this->banner ? File::get($this->banner->getRealPath()) : null, 'filename' => $this->banner ? $this->banner->getClientOriginalName() : ''],
            ['name' => 'app_thumb', 'contents' => $this->app_thumb ? File::get($this->app_thumb->getRealPath()) : null, 'filename' => $this->app_thumb ? $this->app_thumb->getClientOriginalName() : ''],
            ['name' => 'app_banner', 'contents' => $this->app_banner ? File::get($this->app_banner->getRealPath()) : null, 'filename' => $this->app_banner ? $this->app_banner->getClientOriginalName() : ''],
            [
                'name' => 'products',
                'contents' => $this->products
            ]
        ];
    }

    public function delete()
    {
        return $this->client->delete('api/v1/partners/' . $this->partner_id .'/collection/' . $this->collection_id);
    }

}
