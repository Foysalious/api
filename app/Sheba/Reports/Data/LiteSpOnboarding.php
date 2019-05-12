<?php namespace Sheba\Reports\Data;

use App\Models\HyperLocal;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Reports\ReportData;

class LiteSpOnboarding extends ReportData
{
    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request)
    {
        return $this->getPartners($request)->map(function($partner) {
            $master_categories = [];
            $partner->categories->each(function ($category) use (&$master_categories) {
                array_push($master_categories, $category->parent->name);
            });

            $geo = $partner->geo_informations ? json_decode($partner->geo_informations) : null;
            $location = $geo ? $this->getLocation($geo->lat, $geo->lng) : null;

            return [
                'partner_id' => $partner->id,
                'partner_name' => $partner->name,
                'location' => $location ? $location->name : 'N/A',
                'address' => !empty($partner->address) ? $partner->address : 'N/A',
                'lat' => $geo ? $geo->lat : null,
                'lng' => $geo ? $geo->lng : null,
                'master_categories' => !empty($partner->categories) ? collect($master_categories)->unique()->implode(', ') : 'N/A',
                'categories' => !empty($partner->categories) ? $partner->categories->where('publication_status', 1)->pluck('name', 'id')->implode(', ') : 'N/A',
                'ambassador_id' => !empty($partner->affiliate->ambassador_id) ? $partner->affiliate->ambassador_id : 'N/A',
                'ambassador_name' => !empty($partner->affiliate->ambassador_id) ? $partner->affiliate->ambassador->profile->name : 'N/A',
                'agent_id' => $partner->affiliate_id,
                'agent_name' => $partner->affiliate->profile->name,
                'resource_name' => $partner->resources->first() ? $partner->resources->first()->name : 'N/A',
                'resource_mobile' => $partner->resources->first() ? $partner->resources->first()->mobile : 'N/A',
                'status' => $partner->moderation_status,
                'moderator_name' => $partner->moderator_id ? $partner->moderator->profile->name : 'N/A',
                'created_at' => $partner->created_at->format('d M Y H:i'),
            ];
        })->toArray();
    }

    /**
     * @param Request $request
     * @return Collection
     */
    private function getPartners(Request $request)
    {
        $partners = Partner::whereNotNull('affiliate_id')
            ->with('affiliate.profile', 'affiliate.ambassador.profile', 'moderator.profile', 'resources', 'categories')
            ->select('id', 'affiliate_id', 'address', 'geo_informations', 'moderator_id', 'moderation_status', 'name', 'created_at')
            ->groupBy('id');

        $partners = $this->notLifetimeQuery($partners, $request->all());

        return $partners->get();
    }

    private function getLocation($lat, $lng)
    {
        $hyper_local = HyperLocal::insidePolygon((double)$lat, (double)$lng)->with('location')->first();
        return $hyper_local ? $hyper_local->location : null;
    }
}