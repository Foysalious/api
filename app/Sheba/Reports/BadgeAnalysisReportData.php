<?php namespace Sheba\Reports;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Partner\Badge\BadgeCalculator;

class BadgeAnalysisReportData extends ReportData
{
    private $calculator;

    public function __construct(BadgeCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request)
    {
        return $this->getPartners($request)->map(function ($partner) {
            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'badge' => strtoupper($partner->badge ?: "")
            ] + $this->calculator->setPartner($partner)->getValuesFromPartner();
        })->toArray();
    }
    
    /**
     * @param Request $request
     * @return Collection
     */
    private function getPartners(Request $request)
    {
        $partners = Partner::verified();

        if($request->filled('badges')) {
            $badges = explode(',', $request->badges);
            $partners->whereIn('badge', $badges);
        }
        if($request->filled('partners')) {
            $partner_ids = explode(',', $request->partners);
            $partners->whereIn('id', $partner_ids);
        }
        if($request->filled('packages')) {
            $packages = explode(',', $request->packages);
            $partners->whereIn('package_id', $packages);
        }

        return $partners->get();
    }
}