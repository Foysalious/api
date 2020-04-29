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
        return [
            'name' => $resource->profile->name,
            'picture' => $resource->profile->pro_pic,
            'is_verified' => $resource->is_verified,
            'rating' => $this->reviewRepository->getAvgRating($resource->reviews),
            'notification_count' => $resource->notifications()->where('is_seen', 0)->count(),
            'balance' => 1000
        ];

    }
}