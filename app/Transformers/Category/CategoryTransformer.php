<?php namespace App\Transformers\Category;


use App\Models\Category;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    public function transform(Category $category)
    {
        dd($category);
        $usps = $category->usps()->select('name')->get();
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->getSlug(),
            'avg_rating' => 4.75,
            'total_ratings' => 500,
            'total_services' => 200,
            'total_resources' => 40,
            'total_served_orders' => 1000,
            'banner' => $category->banner,
            'usp' => count($usps) > 0 ? $usps->pluck('name')->toArray() : [],
            'overview' => $category->contents  ? $category->contents : []
        ];
    }
}