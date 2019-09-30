<?php


namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    public function transform($category)
    {
        return [
            'id' => (int)$category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'icon' => $category->icon,
            'picture' => $category->thumb
        ];
    }
}