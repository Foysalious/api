<?php namespace Sheba\Reports\Data;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Reports\ReportData;

class PartnerLocationCoverage extends ReportData
{
    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request)
    {
        return $this->getPartners($request)->map(function($partner) {
            $geo = $partner->geo_informations ? json_decode($partner->geo_informations) : null;
            return [
                'partner_id' => $partner->id,
                'partner_name' => $partner->name,
                'lat' => $geo ? $geo->lat : null,
                'lng' => $geo ? $geo->lng : null,
                'radius' => $geo ? (!empty($geo->radius) ? $geo->radius : null) : null,
            ];
        })->toArray();
    }

    /**
     * @param Request $request
     * @return Collection
     */
    private function getPartners(Request $request)
    {
        $partner_ids = explode(',', collect($request->partner_ids)->first());
        return Partner::select('id', 'name', 'geo_informations')->whereIn('id', $partner_ids)->get();
    }
}