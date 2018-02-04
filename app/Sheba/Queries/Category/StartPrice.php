<?php

namespace App\Sheba\Queries\Category;

use App\Models\Category;

class StartPrice
{
    public $category;
    public $start_price;

    public function __construct($category)
    {
        $this->category = $category instanceof Category ? $category : Category::find((int)$category);
    }

    public function calculate()
    {
        if ($this->isMasterCategory()) {
            $services = $this->category->children->load(['services' => function ($q) {
                $q->published();
            }]);
            dd($services);
        }
    }

    private function isMasterCategory()
    {
        return $this->category->parent_id == null;
    }
}