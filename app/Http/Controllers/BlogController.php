<?php

namespace App\Http\Controllers;
use App\Transformers\BlogTransformer;
use App\Transformers\OfferTransformer;
use GuzzleHttp\Client;
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
            $limit = 3;
            if ($request->has("limit")) $limit = $request->limit;
            $url = env('SHEBA_SMANAGER_LINK') . "/blog/wp-json/wp/v2/posts?per_page=".$limit;
            $response = (new Client())->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);
            $blogs = $this->getBlogsWithFormation($response);
            if (count($blogs) > 0) return api_response($request, $blogs, 200, ['blogs' => $blogs]);
            else return api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getBlogsWithFormation($blogs) {
        $blogs_collection = collect($blogs);
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $resource = new Collection($blogs_collection, new BlogTransformer());
        return $manager->createData($resource)->toArray()['data'];
    }
}
