<?php namespace Sheba\Recommendations\HighlyDemands\Categories;

use Sheba\Dal\Category\Category;
use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Basic extends Recommender
{
    private $numberOfCategories = 10;

    protected function recommendation()
    {
        $secondaries_categories = collect();
        $start_end_of_last_six_month = $this->timeFrame->forSixMonth(Carbon::now())->getArray();

        Job::whereBetween('jobs.created_at', $start_end_of_last_six_month)->groupBy('category_id')
            ->join('categories', 'categories.id', '=', 'jobs.category_id')
            ->join('partner_orders', 'partner_orders.id', '=', 'jobs.partner_order_id')
            ->join('orders', 'orders.id', '=', 'partner_orders.order_id')
            ->select('category_id', DB::raw('count(*) as total'))
            ->where('categories.publication_status', 1)
            ->where('orders.location_id', $this->locationId)
            ->orderBy('total', 'desc')
            ->take($this->numberOfCategories)
            ->get()->each(function ($job) use ($secondaries_categories) {
                /** @var Category $secondary_category */
                $secondary_category = $job->category()->first();
                $secondaries_categories->push([
                    'id'          => $secondary_category->id,
                    'name'        => $secondary_category->name,
                    'thumb'       => $secondary_category->thumb,
                    'thumb_sizes' => getResizedUrls($secondary_category->thumb, 180, 270),
                    'banner'      => $secondary_category->banner,
                    'app_thumb'   => $secondary_category->app_thumb,
                    'app_banner'  => $secondary_category->app_banner,
                    'icon'        => $secondary_category->icon,
                    'icon_png'    => $secondary_category->icon_png,
                    'slug'        => $secondary_category->getSlug()
                ]);
            });

        return $secondaries_categories->values();
    }
}
