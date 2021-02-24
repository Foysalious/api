<?php


namespace Sheba\NeoBanking\DTO\Fundamentals;


use Sheba\NeoBanking\DTO\FormItem;
use Sheba\NeoBanking\DTO\FormItemList;

class DoubleView extends FormItem
{
    protected $field_type="doubleView";
    protected $name;
    protected $id;
    /** @var FormItemList $views */
    protected $views;


    /**
     * @param mixed $name
     * @return DoubleView
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $id
     * @return DoubleView
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $views
     * @return DoubleView
     */
    public function setViews(FormItemList $views)
    {
        $this->views = $views;
        return $this;
    }
}
