<?php namespace Sheba\Repositories\Business;

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

    public function allProcurement()
    {
        return $this->model->with('tags')->limit(10)->orderBy('id', 'desc');
    }

    public function filterWithTag($tag_id)
    {
        return $this->model->whereHas('tags', function ($query) use ($tag_id) {
            $query->where('id', $tag_id);
        });
    }

    public function filterWithCategory($category_id)
    {
        return $this->model->where('category_id', $category_id);
    }

    public function filterWithSharedTo($shared_to)
    {
        return $this->model->where('shared_to', $shared_to);
    }

    public function filterWithEndDate($start_date, $end_date)
    {
        return $this->model->whereBetween('procurement_end_date', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
    }

    public function filterWithBudget($budget)
    {
        return $this->model->where('estimated_price', $budget);
    }
}