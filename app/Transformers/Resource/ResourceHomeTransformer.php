<?php namespace App\Transformers\Resource;

use App\Repositories\ReviewRepository;
use League\Fractal\TransformerAbstract;

class ResourceHomeTransformer extends TransformerAbstract
{
    private $reviewRepository;

    public function __construct()
    {
        $this->reviewRepository = new ReviewRepository();
    }

    public function transform($resource)
    {
        $resource_partner = $resource->firstPartner();
        $geo = $resource_partner && $resource_partner->geo_informations ? json_decode($resource_partner->geo_informations) : null;
        return [
            'id' => $resource->id,
            'name' => $resource->profile->name,
            'picture' => $resource->profile->pro_pic,
            'is_verified' => $resource->is_verified,
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