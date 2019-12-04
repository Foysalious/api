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
        Job::whereBetween('created_at', $start_end_of_last_six_month)->groupBy('category_id')
            ->select('category_id', DB::raw('count(*) as total'))
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
