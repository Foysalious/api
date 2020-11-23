<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Schema\SchemaCacheRequest;

class SchemaController extends Controller
{
    public function getAllSchemas(Request $request, CacheAside $cache_aside, SchemaCacheRequest $schema_cache)
    {
        $this->validate($request, ['type' => 'required|string|in:service,category', 'type_id' => 'required|numeric']);
        $schema_cache->setType($request->type)->setTypeId($request->type_id);
        $cache_aside->setCacheRequest($schema_cache);
        $data = $cache_aside->getMyEntity();
        if (!$data) return api_response($request, 1, 404);
        return api_response($request, 1, 200, $data);
    }
}