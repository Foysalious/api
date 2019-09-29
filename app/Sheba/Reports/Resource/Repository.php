<?php namespace Sheba\Reports\Resource;

use Sheba\Reports\Repository as BaseRepository;

class Repository extends BaseRepository
{
    public function __construct(Query $query)
    {
        parent::__construct($query);
    }
}