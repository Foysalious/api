<?php namespace Sheba\Reports\PartnerAnalysis;

use App\Models\Partner;
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
        return $this->repo->get()->map(function (Partner $partner) {
            $partner->contact_resource = $partner->getContactResource();
            $partner->resources_count = $partner->resources->unique()->count();
            $partner->verified_resources_count = $partner->getVerifiedResources()->unique()->count();
            return $partner;
        });
    }
}