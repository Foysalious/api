<?php

namespace App\Sheba\DynamicForm;

use Sheba\NeoBanking\Traits\ProtectedGetterTrait;

class CategoryDetails
{
    use ProtectedGetterTrait;

    protected $completionPercentage;
    protected $name;
    private   $last_updated;
    protected $categoryCode;
    protected $categoryId;

    /**
     * @param mixed $completionPercentage
     */
    public function setCompletionPercentage($completionPercentage): CategoryDetails
    {
        $this->completionPercentage = [
            "en" => $completionPercentage,
            "bn" => convertNumbersToBangla($completionPercentage)
        ];
        return $this;
    }

    public function setName($name_en, $name_bn): CategoryDetails
    {
        $this->name = [
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
        $this->categoryCode = $code;
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
    public function getName()
    {
        return $this->name;
    }
}