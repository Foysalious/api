<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Schema\SchemaCache;
use Sheba\Schema\ShebaSchema;

class SchemaController extends Controller
{
    public function getAllSchemas(Request $request, CacheAside $cache_aside, SchemaCache $schema_cache, ShebaSchema $sheba_schema)
    {
        $this->validate($request, ['type' => 'required|string|in:service,category', 'type_id' => 'required|numeric']);
        $schema_cache->setType($request->type)->setTypeId($request->type_id);
        $cache_aside->setCacheObject($schema_cache);
        return api_response($request, true, 200, $cache_aside->getMyEntity());
    }
}