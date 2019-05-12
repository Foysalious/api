<?php namespace Sheba\Reports\PartnerAnalysis;

use Sheba\Reports\Repository as BaseRepository;

class Repository extends BaseRepository
{
    public function __construct(Query $query)
    {
        parent::__construct($query);
    }
}