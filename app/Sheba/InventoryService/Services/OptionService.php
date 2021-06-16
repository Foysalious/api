<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class OptionService
{
    public $partnerId;
    public $modifier;
    public $name;
    public $client;
    public $optionId;

    /**
     * OptionService constructor.
     * @param $client
     */
    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $optionId
     * @return OptionService
     */
    public function setOptionId($optionId)
    {
        $this->optionId = $optionId;
        return $this;
    }

    /**
     * @param mixed $partnerId
     * @return OptionService
     */
    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param mixed $modifier
     * @return OptionService
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * @param mixed $name
     * @return OptionService
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getOptions()
    {
        $url = 'api/v1/partners/'.$this->partnerId.'/options';
        return $this->client->get($url);
    }

    private function makeData()
    {
        $data = [];
        $data['name'] = $this->name;
        $data['modifier']  = $this->modifier;
        $data['partner_id'] = $this->partnerId;
        return $data;
    }

    public function store()
    {
        $data = $this->makeData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/options', $data);
    }

    public function update()
    {
        $data = $this->makeData();
        return $this->client->put('api/v1/partners/'.$this->partnerId.'/options/'.$this->optionId, $data);
    }

    public function delete()
    {
        return $this->client->delete('api/v1/options/'.$this->optionId);
    }
}