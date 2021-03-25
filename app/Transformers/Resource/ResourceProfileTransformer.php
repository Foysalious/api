<?php namespace App\Transformers\Resource;

use App\Repositories\ReviewRepository;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Partner\LeaveStatus;

class ResourceProfileTransformer extends TransformerAbstract
{
    private $reviewRepository;
    /**
     * @var LeaveStatus
     */
    private $leaveStatus;

    public function __construct()
    {
        $this->reviewRepository = new ReviewRepository();
        $this->leaveStatus = new LeaveStatus();
    }

    public function transform($resource)
    {
        $leave_status = $this->leaveStatus->setArtisan($resource)->getCurrentStatus();
        return [
            'name' => $resource->profile->name,
            'picture' => $resource->profile->pro_pic,
            'partner_name' => $resource->firstPartner() ? $resource->firstPartner()->name : null,
            'is_verified' => $resource->is_verified,
            'is_online' => $leave_status['status'] ? 0 : 1,
            'nid_no' => $resource->nid_no,
            'phone' => $resource->profile->mobile,
            'rating' => $this->reviewRepository->getAvgRating($resource->reviews),
            'experience_in_years' => Carbon::now()->diffInYears($resource->created_at),
            'orders_served' => $resource->totalServedJobs(),
        ];
    }
}
