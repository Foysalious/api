<?php namespace Sheba\Reports;

use Illuminate\Support\Collection;

abstract class Repository
{
    protected $chunkSize = 5000;

    /** @var Query */
    protected $query;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * @return Collection
     */
    public function get()
    {
        $query = $this->query->build();
        $data = collect();
        $query->orderBy('id')->chunk($this->chunkSize, function($chunk) use(&$data) {
            $data = $data->merge($chunk);
        });
        return $data;
    }
}