<?php


namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class CollectionService
{
    protected $name;
    protected $description;
    protected $partner_id, $is_published, $thumb, $banner, $app_thumb, $app_banner, $sharding_id;
    protected $collection_id;

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

    public function getAllCollection()
    {
        return $this->client->get('api/v1/partners/' . $this->partner_id . '/collection/' . $this->collection_id);
    }

    public function store()
    {
        $data = $this->makeCreateData();
        return $this->client->post('api/v1/partners/' . $this->partner_id . '/collection', $data);
    }

    public function update()
    {
        $data = $this->makeCreateData();
        return $this->client->put('api/v1/collection/' . $this->collection_id, $data);
    }

    public function getDetails()
    {
        return $this->client->get('api/v1/partners/' . $this->partner_id . '/collection/'. $this->collection_id);
    }

    private function makeCreateData()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'partner_id' => $this->partner_id,
            'sharding_id' => $this->sharding_id,
            'thumb' => $this->thumb,
            'banner' => $this->banner,
            'app_thumb' => $this->app_thumb,
            'app_banner' => $this->app_banner,
            'is_published' => $this->is_published
        ];
    }

    public function delete()
    {
        return $this->client->delete('api/v1/collection/' . $this->collection_id);
    }

}