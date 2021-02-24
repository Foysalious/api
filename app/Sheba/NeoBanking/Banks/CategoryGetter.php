<?php


namespace Sheba\NeoBanking\Banks;


use Sheba\NeoBanking\DTO\BankFormCategory;

class CategoryGetter
{
    /** @var BankFormCategory $category */
    protected $category;

    public function setCategory(BankFormCategory $category)
    {
        $this->category = $category;
        return $this;
    }

    public function toArray()
    {
        return ['title' => $this->category->getTitle(), 'form_items' => $this->category->getData(), 'completion' => $this->category->completion()];
    }

    public function getFormItems()
    {
        return $this->category->getData();
    }

}
