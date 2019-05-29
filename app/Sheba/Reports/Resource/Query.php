<?php namespace Sheba\Reports\Resource;

use App\Models\Resource;
use Sheba\Reports\Query as BaseQuery;

class Query extends BaseQuery
{
    public function build()
    {
        return $this->buildNormal();
    }

    private function buildOptimized()
    {

    }

    private function buildNormal()
    {
        return Resource::with('partners', 'reviews');
    }
}