<?php namespace App\Transformers\Resource;

use App\Repositories\ReviewRepository;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ResourceProfileTransformer extends TransformerAbstract
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
            'partner_name' => $resource->firstPartner()->name,
            'is_verified' => $resource->is_verified,
            'nid_no' => $resource->nid_no,
            'phone' => $resource->profile->mobile,
            'rating' => $this->reviewRepository->getAvgRating($resource->reviews),
            'experience_in_years' => Carbon::now()->diffInYears($resource->created_at),
            'orders_served' => $resource->totalServedJobs(),
        ];
    }
}