<?php namespace App\Transformers\Category;


use App\Models\Category;
use Carbon\Carbon;
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
            'is_trending' => $category->is_trending ? ['last_week_order_count' => $this->getLastWeekOrderCount($category)] : null,
            'service_title' => $category->service_title,
            'popular_service_description' => $category->popular_service_description,
            'other_service_description' => $category->other_service_description,
            'avg_rating' => 4.75,
            'total_ratings' => 500,
            'total_services' => 200,
            'total_resources' => 40,
            'total_served_orders' => 1000,
            'banner' => $category->banner,
            'usp' => count($usps) > 0 ? $usps->pluck('name')->toArray() : null,
            'overview' => $category->contents ? $category->contents : null,
            'details' => $category->long_description,
            'partnership' => $partnership ? [
                'title' => $partnership->title,
                'short_description' => $partnership->short_description,
                'images' => count($partnership->slides) > 0 ? $partnership->slides->pluck('thumb') : []
            ] : null,
            'faqs' => $category->faqs ? json_decode($category->faqs) : null,
            'gallery' => count($galleries) > 0 ? $galleries : null,
            'blog' => count($blog_posts) > 0 ? $blog_posts : null,
        ];
    }

    private function getLastWeekOrderCount($category)
    {
        $now = Carbon::now();
        $week_end_date = $now->format('Y-m-d');
        $week_start_date = $now->subDays(7)->format('Y-m-d');
        $jobs = $category->jobs();
        if ($week_start_date && $week_end_date) {
            $jobs->whereBetween('created_at', [$week_start_date . ' 00:00:00', $week_end_date . ' 23:59:59']);
        }
        return $jobs->count();
    }
}