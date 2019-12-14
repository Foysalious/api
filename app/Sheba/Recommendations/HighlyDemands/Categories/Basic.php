<?php namespace Sheba\Recommendations\HighlyDemands\Categories;

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
            ->whereIn('orders.location_id', $this->locationId)
            ->orderBy('total', 'desc')
            ->take($this->numberOfCategories)
            ->get()->each(function ($job) use ($secondaries_categories) {
                $secondaries_categories->push(
                    $job->category()->select('id', 'name', 'app_thumb', 'app_banner', 'icon', 'icon_png')->first()
                );
            });

        return $secondaries_categories->values();
    }
}
