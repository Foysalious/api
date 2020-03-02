<?php namespace App\Transformers\Category;


use App\Models\Category;
use App\Models\Job;
use App\Models\Resource;
use App\Models\Service;
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
        $reviews = $category->reviews()->selectRaw("count(DISTINCT(reviews.id)) as total_ratings,avg(reviews.rating) as avg_rating")->groupBy('reviews.category_id')->first();
        $parent_category = $category->parent;
        $master_category = [];
        if ($parent_category) {
            $master_category = [
                'id' => $parent_category->id,
                'name' => $parent_category->name,
                'slug' => $parent_category->getSlug(),
            ];
        }
        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->getSlug(),
            'thumb' => $category->thumb,
            'app_thumb' => $category->app_thumb,
            'is_trending' => $category->is_trending ? ['last_week_order_count' => $this->getLastWeekOrderCount($category)] : null,
            'master_category' => count($master_category) > 0 ? $master_category : null,
            'service_title' => $category->service_title,
            'popular_service_description' => $category->popular_service_description,
            'other_service_description' => $category->other_service_description,
            'is_auto_sp_enabled' => $category->is_auto_sp_enabled,
            'avg_rating' => $reviews ? round($reviews->avg_rating, 2) : null,
            'total_ratings' => $reviews ? $reviews->total_ratings : null,
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
        return array_merge($data, $this->appendMasterCategoryTag($category));
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


    private function appendMasterCategoryTag(Category $category)
    {
        if ($category->parent_id) {
            return array(
                'total_services' => !$category->parent_id ? $this->getJobs($category) : 200,
                'total_resources' => 40,
                'total_served_orders' => 1000
            );
        }
        $cats = $category->children()->published()->select('id', 'parent_id')->get()->pluck('id')->toArray();
        return array(
            'total_services' => $this->getTotalServices($cats),
            'total_resources' => $this->getTotalResources($cats),
            'total_served_orders' => $this->getTotalCompletedOrders($cats),
        );


    }

    private function getTotalServices(array $category_ids)
    {
        return $service = Service::published()->whereIn('category_id', $category_ids)->count();
    }

    private function getTotalResources(array $category_ids)
    {
        return 40;
    }

    private function getTotalCompletedOrders(array $category_ids)
    {
        $jobs = Job::selectRaw("count(case when status in ('Served')then status end) as total_completed_orders")->whereIn('category_id', $category_ids)
            ->groupBy('jobs.category_id')->first();
        return $jobs ? $jobs->total_completed_orders : 0;

    }

}