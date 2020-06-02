<?php namespace Sheba\Repositories\Business;

use Carbon\Carbon;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Repositories\BaseRepository;
use App\Models\Procurement;

class ProcurementRepository extends BaseRepository implements ProcurementRepositoryInterface
{
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
        return $this->model->with('tags', 'bids')
            ->where('last_date_of_submission', '>=', Carbon::now());
    }

    public function filterWithTag($tag_id)
    {
        return $this->model->whereHas('tags', function ($query) use ($tag_id) {
            $query->where('id', $tag_id);
        });
    }

    public function filterWithCategory($category_ids)
    {
        return $this->model->whereIn('category_id', explode(',',$category_ids));
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
}