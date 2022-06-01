<?php

namespace App\Sheba\DynamicForm;

use Sheba\NeoBanking\Traits\ProtectedGetterTrait;

class CategoryDetails
{
    use ProtectedGetterTrait;

    protected $completion_percentage;
    protected $title;
    private   $last_updated;
    protected $category_code;
    protected $categoryId;

    /**
     * @param mixed $completion_percentage
     */
    public function setCompletionPercentage($completion_percentage): CategoryDetails
    {
        $this->completion_percentage = [
            "en" => $completion_percentage,
            "bn" => convertNumbersToBangla($completion_percentage)
        ];
        return $this;
    }

    public function setTitle($name_en, $name_bn): CategoryDetails
    {
        $this->title = [
            "en" => $name_en,
            "bn" => $name_bn,
        ];
        return $this;
    }

    /**
     * @param mixed $last_updated
     */
    public function setLastUpdated($last_updated): CategoryDetails
    {
        $this->last_updated = $last_updated;
        return $this;
    }

    /**
     * @param mixed $code
     */
    public function setCategoryCode($code): CategoryDetails
    {
        $this->category_code = $code;
        return $this;
    }

    /**
     * @param mixed $categoryId
     * @return CategoryDetails
     */
    public function setCategoryId($categoryId): CategoryDetails
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}