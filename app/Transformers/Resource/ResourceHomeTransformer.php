<?php namespace App\Transformers\Resource;

use App\Repositories\ReviewRepository;
use League\Fractal\TransformerAbstract;
use Sheba\Partner\LeaveStatus;

class ResourceHomeTransformer extends TransformerAbstract
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
        $resource_partner = $resource->firstPartner();
        $geo = $resource_partner && $resource_partner->geo_informations ? json_decode($resource_partner->geo_informations) : null;
        $leave_status = $this->leaveStatus->setArtisan($resource)->getCurrentStatus();
        return [
            'id' => $resource->id,
            'name' => $resource->profile->name,
            'picture' => $resource->profile->pro_pic,
            'is_verified' => $resource->is_verified,
            'is_online' => $leave_status['status'] ? 0 : 1,
            'rating' => $this->reviewRepository->getAvgRating($resource->reviews),
            'notification_count' => $resource->notifications()->where('is_seen', 0)->count(),
            'balance' => $resource->totalWalletAmount(),
            'partner_id' => $resource_partner ? $resource_partner->id : null,
            'geo_informations' => $geo ? [
                'lat' => (double)$geo->lat,
                'lng' => (double)$geo->lng,
                'radius' => !empty($geo->radius) ? (double)$geo->radius : null
            ] : null
        ];

    }
}