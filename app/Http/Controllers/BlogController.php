<?php

namespace App\Http\Controllers;
use App\Transformers\BlogTransformer;
use App\Transformers\OfferTransformer;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Redis;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Dal\BlogPost\BlogPost;
use Throwable;

class BlogController extends Controller
{
    public function index(Request $request) {
        try {
            $key_name = 'smanager_blogs';
            $cache_blogs = json_decode(Redis::get($key_name));
            if ($cache_blogs) return api_response($request, $cache_blogs, 200, ['data' => $cache_blogs]);
            $limit = 3;
            if ($request->filled("limit")) $limit = $request->limit;
            $url = constants('BLOG_URL') . "/wp-json/wp/v2/posts?orderby=date&order=desc&per_page=".$limit;
            $response = (new Client())->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);
            if(!$response) return api_response($request, null, 401, ['data' => null]);
            $blogs = $this->getBlogsWithFormation($response);
            $data["blogs"] = $this->getBlogsWithFormation($response);
            $data["blog_url"] = constants('BLOG_URL');

            Redis::set($key_name, json_encode($data));
            Redis::expire($key_name, 3600);

            if (count($blogs) > 0) return api_response($request, $data, 200, ['data' => $data]);
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
