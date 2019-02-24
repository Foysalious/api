<?php namespace Sheba\AppSettings\HomePageSetting\DS;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

class ItemSet implements Arrayable, ArrayAccess
{
    /** @var array */
    protected $items = [];

    public function push(Item $item)
    {
        $this->items[] = $item;
        return $this;
    }

    public function toArray()
    {
        $items = [];
        foreach ($this->items as $item) {
            /** @var Item $item */
            $items[] = $item->toArray();
        }
        return $items;
    }
    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }
}