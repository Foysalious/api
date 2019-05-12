<?php namespace Sheba\Reports;

use Illuminate\Database\Eloquent\Builder;

abstract class Query
{
    /**
     * @return Builder
     */
    abstract public function build();
}