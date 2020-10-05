<?php namespace Sheba\Resource\Review;


use App\Models\Resource;
use Sheba\Dal\Review\ReviewRepositoryInterface;

class ReviewList
{
    /** @var Resource */
    protected $resource;
    protected $rating;
    protected $categoryId;
    protected $offset;
    protected $limit;
    /** @var ReviewRepositoryInterface */
    private $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {

        $this->reviewRepository = $reviewRepository;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
        return $this;
    }

    public function setCategory($id)
    {
        $this->categoryId = $id;
        return $this;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function getReviews()
    {
        $reviews = $this->reviewRepository->getReviews($this->resource->id);
        $reviews = $this->filterReviews($reviews);
        return $reviews->get();
    }

    private function filterReviews($reviews)
    {
        if ($this->rating) $reviews = $reviews->where('rating', $this->rating);
        if ($this->categoryId) $reviews = $reviews->where('category_id', $this->categoryId);
        if ($this->limit) $reviews = $reviews->skip($this->offset)->take($this->limit);
        return $reviews;
    }
}