<?php namespace Sheba\Resource\Review;


use App\Models\Resource;
use App\Repositories\ReviewRepository;

class RatingInfo
{
    protected $resource;
    /** @var ReviewRepository */
    private $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function getRatingInfo()
    {
        $partner = $this->resource->firstPartner();
        $reviews = $this->resource->reviews;
        $total_order = $this->resource->totalJobs();
        $breakdown = array();
        $avg_rating = null;
        if (count($reviews) > 0) {
            $breakdown = $this->reviewRepository->getReviewBreakdown($reviews);
            $partner = $this->reviewRepository->getGeneralReviewInformation($partner);
            $avg_rating = $this->reviewRepository->getAvgRating($reviews);
            removeRelationsAndFields($partner);
        }

        $info = array(
            'rating' => $avg_rating,
            'total_reviews' => $reviews->count(),
            'total_order' => $total_order,
            'breakdown' => $breakdown
        );
        return $info;
    }
}