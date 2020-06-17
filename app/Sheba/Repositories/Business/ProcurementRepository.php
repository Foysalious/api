<?php namespace Sheba\Repositories\Business;

use Carbon\Carbon;
use Sheba\Business\Procurement\ProcurementFilterRequest;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Repositories\BaseRepository;
use App\Models\Procurement;

class ProcurementRepository extends BaseRepository implements ProcurementRepositoryInterface
{
    /**
     * ProcurementRepository constructor.
     * @param Procurement $procurement
     */
    public function __construct(Procurement $procurement)
    {
        parent::__construct();
        $this->setModel($procurement);
    }

    public function ofBusiness($business_id)
    {
        return $this->model->where('owner_id', $business_id)->where('owner_type', "App\\Models\\Business");
    }

    public function builder()
    {
        return $this->model->newQuery();
    }

    public function getProcurementFilterByLastDateOfSubmission()
    {
        return $this->model
            ->with('tags', 'bids')
            ->where('last_date_of_submission', '>=', Carbon::now());
    }

    public function filterWithTag($tag_ids)
    {
        return $this->model->whereHas('tags', function ($query) use ($tag_ids) {
            $query->whereIn('id', json_decode($tag_ids));
        });
    }

    public function filterWithCategory($category_ids)
    {
        return $this->model->whereIn('category_id', json_decode($category_ids));
    }

    public function filterWithSharedTo($shared_to)
    {
        return $this->model->where('shared_to', $shared_to);
    }

    public function filterWithEndDate($start_date, $end_date)
    {
        return $this->model->whereBetween('procurement_end_date', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
    }

    public function filterWithEstimatedPrice($min_price, $max_price)
    {
        return $this->model->whereBetween('estimated_price', [$min_price, $max_price]);
    }

    /**
     * @param ProcurementFilterRequest $procurement_filter_request
     * @return mixed
     */
    public function getProcurementFilterBy(ProcurementFilterRequest $procurement_filter_request)
    {
        $categories = $procurement_filter_request->getCategoriesId();
        $shared_to = $procurement_filter_request->getSharedTo();
        $min_price = $procurement_filter_request->getMinPrice();
        $max_price = $procurement_filter_request->getMaxPrice();
        $start_date = $procurement_filter_request->getStartDate();
        $end_date = $procurement_filter_request->getEndDate();
        $tags = $procurement_filter_request->getTagsId();
        $search = $procurement_filter_request->getSearchQuery();
        $limit = $procurement_filter_request->getLimit();

        $base_query = $this->model->with('tags', 'bids')->where('last_date_of_submission', '>=', Carbon::now())
            ->where(function ($query) use ($categories, $shared_to, $min_price, $max_price, $start_date, $end_date, $tags) {
                $query->when($categories, function ($category_query) use ($categories) {
                    return $category_query->whereIn('category_id', $categories)->orWhereNull('category_id');
                })
                    ->when($shared_to, function ($share_to_query) use ($shared_to) {
                        return $share_to_query->orWhereIn('shared_to', $shared_to);
                    })
                    ->when($min_price, function ($price_query) use ($min_price, $max_price) {
                        return $price_query->orWhereBetween('estimated_price', [$min_price, $max_price]);
                    })
                    ->when($start_date, function ($date_query) use ($start_date, $end_date) {
                        return $date_query->orWhereBetween('procurement_end_date', [$start_date, $end_date]);
                    })
                    ->when($tags, function ($tag_query) use ($tags) {
                        return $tag_query
                            /*->whereDoesntHave('tags')*/
                            ->orWhereHas('tags', function ($query) use ($tags) {
                                $query->whereIn('id', $tags);
                            });
                    });
            });

        if ($search) {
            $today = Carbon::today()->endOfDay()->timestamp;
            $procurements = $this->model->search($search, ['filters' => "last_date_of_submission_timestamp >= $today"]);
            $procurements_id = (!empty($procurements['hits'])) ? collect($procurements['hits'])->pluck('id')->toArray() : [];
            $base_query = $base_query->whereIn('id', $procurements_id);
        }

        if ($categories) $base_query->orderBy('category_id', 'desc');
        if ($limit) $base_query->limit($limit);

        return $base_query->orderBy('id', 'desc')->get();
    }

    /**
     * @param $status
     * @return mixed
     */
    public function filterWithStatus($status)
    {
        if ($status === 'draft') return $this->model->where('is_published', 0);
        if ($status === 'expired') return $this->model->where('last_date_of_submission', '<', Carbon::now());
        if ($status === 'open') return $this->model->where('status', 'pending');
        if ($status === 'hired') return $this->model->where('status', 'accepted');
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public function filterWithCreatedAt($start_date, $end_date)
    {
        return $this->model->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);

    }

    public function getProcurementWhereTitleBudgetNotNull(ProcurementFilterRequest $procurement_filter_request)
    {
        $limit = $procurement_filter_request->getLimit();

        $base_query = $this->model
            ->where('last_date_of_submission', '>=', Carbon::now())
            ->whereNotNull('title')
            ->whereNotNull('estimated_price');
        if ($limit) $base_query->limit($limit);

        return $base_query->orderBy('id', 'desc')->get();
    }
}
