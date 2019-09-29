<?php namespace Sheba\Reports\Resource;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Reports\ReportData;

class Getter extends ReportData
{
    /** @var Repository */
    private $repo;

    public function __construct(Repository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param Request $request
     * @return Collection
     */
    public function get(Request $request)
    {
        return $this->repo->get();
    }
}