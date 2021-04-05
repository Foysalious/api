<?php namespace Sheba\Resource;

use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\Resource;
use App\Models\ResourceEmployment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;

class ResourceRemover
{
    use ModificationFields;

    private $resource;
    private $partner;

    public function remove(Resource $resource, Partner $partner)
    {
        $this->resource = $resource;
        $this->partner = $partner;

        $this->saveIntoEmploymentHistory();
        $this->deletePartnerResource();
    }

    private function saveIntoEmploymentHistory()
    {
        $partner_resources = $this->partnerResourceQuery()->get();

        $data = [
            'resource_id' => $this->resource->id,
            'partner_id' => $this->partner->id,
            'joined_at' => $partner_resources->first()->created_at,
            'left_at' => Carbon::now(),
            'worked_as' => json_encode($this->resource->typeIn($this->partner)),
            'categories' => $this->getPartnerResourceCategories($partner_resources)->pluck('category_id')->toJson(),
            'jobs_served' => $this->getServedJobCount(),
            'avg_rating' => $this->getAvgRating(),
            'got_complain' => $this->getComplainsCount(),
        ];
        ResourceEmployment::create($this->withBothModificationFields($data));
    }

    private function deletePartnerResource()
    {
        $this->partnerResourceQuery()->delete();
    }

    private function partnerResourceQuery()
    {
        return PartnerResource::where(['resource_id' => $this->resource->id, 'partner_id' => $this->partner->id])
            ->whereNotIn('resource_type', ['Admin', 'Owner']);
    }

    private function getPartnerResourceCategories($partner_resources)
    {
        return DB::table('category_partner_resource')->where('partner_resource_id', $partner_resources->pluck('id')->toArray())->get();
    }

    private function getServedJobCount()
    {
        return DB::table('jobs')->whereIn('partner_order_id', function ($q) {
            $q->select('id')->from('partner_orders')->where('partner_id', $this->partner->id);
        })->where('status', 'Served')->where('resource_id', $this->resource->id)->count();
    }

    private function getAvgRating()
    {
        return DB::table('reviews')
            ->where('partner_id', $this->partner->id)
            ->where('resource_id', $this->resource->id)
            ->avg('rating');
    }

    private function getComplainsCount()
    {
        return DB::table('complains')->whereIn('job_id', function ($q1) {
            $q1->select('id')->from('jobs')->whereIn('partner_order_id', function ($q2) {
                $q2->select('id')->from('partner_orders')->where('partner_id', $this->partner->id);
            })->where('resource_id', $this->resource->id);
        })->count();
    }
}