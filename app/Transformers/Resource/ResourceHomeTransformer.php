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
        $geo = $resource->firstPartner()->geo_informations ? json_decode($resource->firstPartner()->geo_informations) : null;
        return [
            'id' => $resource->id,
            'name' => $resource->profile->name,
            'picture' => $resource->profile->pro_pic,
            'is_verified' => $resource->is_verified,
            'rating' => $this->reviewRepository->getAvgRating($resource->reviews),
            'notification_count' => $resource->notifications()->where('is_seen', 0)->count(),
            'balance' => $resource->totalWalletAmount(),
            'partner_id' => $resource->firstPartner()->id,
            'geo_informations' => [
                'lat' => $geo ? (double)$geo->lat : null,
                'lng' => $geo ? (double)$geo->lng : null,
                'radius' => $geo ? (!empty($geo->radius) ? (double)$geo->radius : null) : null,
            ]
        ];

    }
}