<?php

namespace App\Http\Controllers;
use App\Transformers\BlogTransformer;
use App\Transformers\OfferTransformer;
use Illuminate\Http\Request;

use App\Http\Requests;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Dal\BlogPost\BlogPost;
use Throwable;

class BlogController extends Controller
{
    public function index(Request $request) {
        try {
            list($offset, $limit) = calculatePagination($request);
            $blogs = $this->getBlogsWithFormation(BlogPost::orderBy('id', 'desc')->skip($offset)->take($limit)->get());
            if (count($blogs) > 0) return api_response($request, $blogs, 200, ['blogs' => $blogs]);
            else return api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getBlogsWithFormation($blogs) {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $resource = new Collection($blogs, new BlogTransformer());
        return $manager->createData($resource)->toArray()['data'];
    }
}
