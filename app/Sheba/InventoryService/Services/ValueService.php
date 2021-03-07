<?php namespace App\Sheba\InventoryService\Services;


use App\Sheba\InventoryService\InventoryServerClient;

class ValueService
{
    protected $partnerId;
    protected $modifier;
    protected $name;
    protected $client;
    protected $optionId;
    protected $valueId;
    protected $values;

    /**
     * ValueService constructor.
     * @param $client
     */
    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $partnerId
     * @return ValueService
     */
    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param mixed $modifier
     * @return ValueService
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * @param mixed $name
     * @return ValueService
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $valueId
     * @return ValueService
     */
    public function setValueId($valueId)
    {
        $this->valueId = $valueId;
        return $this;
    }

    /**
     * @param mixed $optionId
     * @return ValueService
     */
    public function setOptionId($optionId)
    {
        $this->optionId = $optionId;
        return $this;
    }

    /**
     * @param $values
     * @return ValueService
     */
    public function setValues($values)
    {
        $this->values = $values;
        return $this;
    }

    private function makeData()
    {
        $data = [];
        $data['name'] = $this->name;
        $data['modifier']  = $this->modifier;
        $data['partner_id'] = $this->partnerId;
        return $data;
    }

    private function makeValuesData()
    {
        $data = [];
        $data['values'] = $this->values;
        $data['modifier']  = $this->modifier;
        $data['partner_id'] = $this->partnerId;
        return $data;
    }

    public function store()
    {
        $data = $this->makeValuesData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/options/'.$this->optionId.'/values', $data);
    }

    public function update()
    {
        $data = $this->makeData();
        return $this->client->put('api/v1/partners/'.$this->partnerId.'/values/'.$this->valueId, $data);
    }

    public function delete()
    {
        return $this->client->delete('api/v1/values/'.$this->valueId);
    }

}