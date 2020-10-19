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
        $reviews = $this->resource->reviews;
        if (count($reviews) === 0) return null;
        $total_order = $this->resource->totalJobs();

        $compliment_counts = $reviews->pluck('rates')->filter(function ($rate) {
            return $rate->count();
        })->flatten()->groupBy('rate_answer_id')->map(function ($answer, $index) {
            return $answer->first()->answer ? [
                'id' => $index,
                'name' => $answer->first()->answer->answer,
                'badge' => $answer->first()->answer->badge,
                'asset' => $answer->first()->answer->asset,
                'count' => $answer->count(),
            ]:null;
        })->filter(function ($value) {
            return !!($value);
        })->toArray();
        $compliment_counts = array_values($compliment_counts);
        $breakdown = $this->reviewRepository->getReviewBreakdown($reviews);
        $avg_rating = $this->reviewRepository->getAvgRating($reviews);
        return array(
            'rating' => $avg_rating,
            'total_reviews' => $reviews->count(),
            'total_order' => $total_order,
            'breakdown' => $breakdown,
            'compliments' => $compliment_counts
        );
    }
}