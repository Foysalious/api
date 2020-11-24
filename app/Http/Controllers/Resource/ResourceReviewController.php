<?php namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Resource\Review\ReviewList;

class ResourceReviewController extends Controller
{
    public function index(Request $request, ReviewList $reviewList)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $reviewList->setResource($resource);
        list($offset, $limit) = calculatePagination($request);
        if ($request->has('limit')) $reviewList = $reviewList->setOffset($offset)->setLimit($limit);
        if ($request->has('rating')) $reviewList->setRating($request->rating);
        if ($request->has('category')) $reviewList->setCategory($request->category);
        $reviews = $reviewList->getReviews();
        return api_response($request, $reviews, 200, ['reviews' => $reviews]);
    }
}
