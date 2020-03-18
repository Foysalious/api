<?php namespace App\Http\Controllers\Resource;

use App\Models\Resource;
use App\Transformers\CustomSerializer;
use App\Transformers\Resource\ResourceProfileTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class ResourceController extends Controller
{
    public function getProfile(Request $request)
    {
            $resource = Resource::find(1300); //TODO: Need to get resource_id from Auth Middleware
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Item($resource, new ResourceProfileTransformer());
            $profile = $fractal->createData($resource)->toArray()['data'];

            return api_response($request, $profile, 200, ['profile' => $profile]);
    }
}
