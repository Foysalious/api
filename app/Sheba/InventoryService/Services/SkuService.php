<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class SkuService
{
    private $skuIds;
    /** @var int */
    private $channelId;
    /** @var InventoryServerClient */
    private $client;

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $skuIds
     * @return SkuService
     */
    public function setSkuIds($skuIds): SkuService
    {
        $this->skuIds = $skuIds;
        return $this;
    }

    /**
     * @param int $channelId
     * @return $this
     */
    public function setChannelId(int $channelId): SkuService
    {
        $this->channelId = $channelId;
        return $this;
    }

    public function getSkus($partnerId)
    {
        $url = 'api/v1/partners/' . $partnerId . '/skus?';
        if (isset($this->skuIds)) $url .= 'skus='.$this->skuIds.'&';
        if (isset($this->channelId)) $url .= 'channel_id='.$this->channelId.'&';
        return $this->client->get($url);
    }
}