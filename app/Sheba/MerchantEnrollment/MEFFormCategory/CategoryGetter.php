<?php

namespace Sheba\MerchantEnrollment\MEFFormCategory;

class CategoryGetter
{
    /** @var MEFFormCategory $category */
    protected $category;

    public function setCategory(MEFFormCategory $category): CategoryGetter
    {
        $this->category = $category;
        return $this;
    }

    public function toArray(): array
    {
        return ['title' => $this->category->getTitle(), 'form_items' => $this->category->getData(), 'completion' => $this->category->completion()];
    }

    public function getFormItems()
    {
        return $this->category->getData();
    }
}