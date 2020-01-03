<?php namespace App\Transformers\Category;


use App\Models\Category;
use League\Fractal\TransformerAbstract;
use DB;

class CategoryTransformer extends TransformerAbstract
{
    public function transform(Category $category)
    {
        $usps = $category->usps()->select('name')->get();
        $partnership = $category->partnership;
        $galleries = $category->galleries()->select('id', DB::Raw('thumb as image'))->get();
        $blog_posts = $category->blogPosts()->select('id', 'title', 'short_description', DB::Raw('thumb as image'), 'target_link')->get();
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->getSlug(),
            'thumb' => $category->thumb,
            'app_thumb' => $category->app_thumb,
            'avg_rating' => 4.75,
            'total_ratings' => 500,
            'total_services' => 200,
            'total_resources' => 40,
            'total_served_orders' => 1000,
            'banner' => $category->banner,
            'usp' => count($usps) > 0 ? $usps->pluck('name')->toArray() : [],
            'overview' => $category->contents ? $category->contents : [],
            'partnership' => $partnership ? [
                'title' => $partnership->title,
                'short_description' => $partnership->short_description,
                'images' => count($partnership->slides) > 0 ? $partnership->slides->pluck('thumb') : []
            ] : null,
            'faqs' => $category->faqs ? json_decode($category->faqs) : null,
            'gallery' => count($galleries) > 0 ? $galleries : null,
            'blog' => count($blog_posts) > 0 ? $blog_posts : null,
            'details' => $category->long_description
        ];
    }
}