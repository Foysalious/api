<?php namespace Sheba\AppSettings\HomePageSetting\DS;

use Carbon\Carbon;
use Sheba\AppSettings\HomePageSetting\Exceptions\UnsupportedSection;
use Sheba\AppSettings\HomePageSetting\Supported\Sections;

class Section
{
    protected $type;
    protected $id;
    protected $name;
    protected $height;
    /** @var  Carbon */
    protected $updatedAt;
    /** @var  array */
    protected $items = [];

    /**
     * @param string $type
     * @return Section
     */
    public function setType($type)
    {
        Sections::validate($type);
        $this->type = $type;
        return $this;
    }

    /**
     * @param int $id
     * @return Section
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $name
     * @return Section
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param int $height
     * @return Section
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param Carbon $updated_at
     * @return Section
     */
    public function setUpdatedAt(Carbon $updated_at)
    {
        $this->updatedAt = $updated_at;
        return $this;
    }

    /**
     * @param Item $item
     * @return Section
     */
    public function pushItem(Item $item)
    {
        $this->items[] = $item;
        return $this;
    }

    public function toArray()
    {
        $items = $this->convertItemsToArray();
        return [
            'item_type' => $this->type,
            'item_id' => $this->id,
            'name' => $this->name,
            'data' => $items ? $items : null,
            'updated_at' => $this->updatedAt ? $this->updatedAt->toDateTimeString() : null,
            'updated_at_timestamp' => $this->updatedAt ? $this->updatedAt->timestamp : null,
        ];
    }

    private function convertItemsToArray()
    {
        $items = [];
        foreach ($this->items as $item) {
            /** @var Item $item */
            $items[] = $item->toArray();
        }
        return $items;
    }
}