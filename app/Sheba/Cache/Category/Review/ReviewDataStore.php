<?php namespace Sheba\Cache\Category\Review;


use App\Models\Category;
use App\Models\Review;
use App\Models\ReviewQuestionAnswer;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class ReviewDataStore implements DataStoreObject
{
    /** @var ReviewCacheRequest */
    private $reviewCacheRequest;

    public function setCacheRequest(CacheRequest $request)
    {
        $this->reviewCacheRequest = $request;
        return $this;
    }

    public function generate()
    {
        $category = Category::find($this->reviewCacheRequest->getCategoryId());
        if (!$category) return ['code' => 404, 'message' => 'No reviews found'];
        $reviews = ReviewQuestionAnswer::select('reviews.category_id', 'customer_id', 'partner_id', 'reviews.rating', 'review_title')
            ->selectRaw("partners.name as partner_name,profiles.name as customer_name,rate_answer_text as review,review_id as id,pro_pic as customer_picture,jobs.created_at as order_created_at")
            ->join('reviews', 'reviews.id', '=', 'review_question_answer.review_id')
            ->join('partners', 'partners.id', '=', 'reviews.partner_id')
            ->join('customers', 'customers.id', '=', 'reviews.customer_id')
            ->join('jobs', 'jobs.id', '=', 'reviews.job_id')
            ->join('profiles', 'profiles.id', '=', 'customers.profile_id')
            ->where('review_type', 'like', '%' . 'Models\\\\Review')
            ->where('review_question_answer.rate_answer_text', '<>', '')
            ->whereIn('reviews.rating', [5])
            ->whereRaw("CHAR_LENGTH(rate_answer_text)>20")
            ->where('reviews.category_id', $category->id)
            ->whereNotNull('profiles.name')
            ->where('profiles.name', '!=', '')
            ->take(10)
            ->orderBy('id', 'desc')
            ->groupBy('customer_id')
            ->get();
        $review_stat = Review::selectRaw("count(DISTINCT(reviews.id)) as total_ratings")
            ->selectRaw("count(DISTINCT(case when rating=5 then reviews.id end)) as total_five_star_ratings")
            ->selectRaw("count(DISTINCT(case when rating=4 then reviews.id end)) as total_four_star_ratings")
            ->selectRaw("count(DISTINCT(case when rating=3 then reviews.id end)) as total_three_star_ratings")
            ->selectRaw("count(DISTINCT(case when rating=2 then reviews.id end)) as total_two_star_ratings")
            ->selectRaw("count(DISTINCT(case when rating=1 then reviews.id end)) as total_one_star_ratings")
            ->selectRaw("avg(reviews.rating) as avg_rating")
            ->selectRaw("reviews.category_id")
            ->where('reviews.category_id', $category->id)
            ->groupBy("reviews.category_id")->first();
        $review_count = ReviewQuestionAnswer::select('reviews.id')
            ->selectRaw("count(reviews.id) as total_reviews")
            ->join('reviews', 'reviews.id', '=', 'review_question_answer.review_id')
            ->where('review_type', 'like', '%' . 'Models\\\\Review')
            ->where('review_question_answer.rate_answer_text', '<>', '')
            ->where('reviews.category_id', $category->id)
            ->groupBy('category_id')
            ->first();
        $info = [
            'avg_rating' => $review_stat && $review_stat->avg_rating ? round($review_stat->avg_rating, 2) : 0,
            'total_review_count' => $review_count && $review_count->total_reviews ? $review_count->total_reviews : 0,
            'total_rating_count' => $review_stat && $review_stat->total_ratings ? $review_stat->total_ratings : 0
        ];
        $group_rating = [
            "1" => $review_stat && $review_stat->total_one_star_ratings ? $review_stat->total_one_star_ratings : null,
            "2" => $review_stat && $review_stat->total_two_star_ratings ? $review_stat->total_two_star_ratings : null,
            "3" => $review_stat && $review_stat->total_three_star_ratings ? $review_stat->total_three_star_ratings : null,
            "4" => $review_stat && $review_stat->total_four_star_ratings ? $review_stat->total_four_star_ratings : null,
            "5" => $review_stat && $review_stat->total_five_star_ratings ? $review_stat->total_five_star_ratings : null,
        ];
        if (count($reviews) == 0) return null;
        return [
            'reviews' => $reviews,
            'group_rating' => $group_rating,
            'info' => $info
        ];
    }

}