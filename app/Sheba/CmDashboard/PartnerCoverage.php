<?php namespace Sheba\CmDashboard;

use App\Models\Location;
use Illuminate\Support\Facades\DB;

class PartnerCoverage
{
    private $category;
    private $service;
    private $serviceCombination = [];

    private $locations = [];
    private $partners = [];

    public function category($category_id)
    {
        $this->category = $category_id;
    }

    public function service($service_id)
    {
        $this->service = $service_id;
    }

    /**
     * @param $combination
     * @throws \Exception
     */
    public function serviceCombination(array $combination)
    {
        if(!$this->service) throw new \Exception('Service is needed to calculate service options');
        $this->serviceCombination = $combination;
    }

    private function init()
    {
        foreach (Location::select('id', 'name')->published()->pluck('name', 'id') as $key => $location) {
            $this->locations[$key] = [
                'name' => $location,
                'partner_count' => 0
            ];
        }
        $this->partners = [];
    }

    public function get()
    {
        $this->init();

        if($this->category) {
            $this->partners = DB::table('category_partner')->select('partner_id')
                ->leftJoin('partners', 'partners.id', '=', 'category_partner.partner_id')
                ->where('category_partner.category_id', $this->category)
                ->where('partners.status', 'Verified')
                ->where('category_partner.is_verified', 1)
                ->pluck('partner_id')
                ->all();
        }

        if($this->service) {
            $service_query = DB::table('partner_service')->select('partner_id', 'options')
                ->leftJoin('partners', 'partners.id', '=', 'partner_service.partner_id')
                ->where('partner_service.service_id', $this->service)
                ->where('partners.status', 'Verified')
                ->where('partner_service.is_published', 1)
                ->where('partner_service.is_verified', 1);

            if(count($this->partners)) {
                $service_query = $service_query->whereIn('partner_service.partner_id', $this->partners);
            }

            $this->partners = $service_query->pluck('partner_id')->all();

            if(count($this->serviceCombination) && count($this->partners)) {
                foreach ($service_query->pluck('options', 'partner_id') as $partner_id => $partner_options) {
                    $partner_options = json_decode($partner_options);
                    $ok = true;
                    foreach ($this->serviceCombination as $key => $option) {
                        if(!in_array($option, $partner_options[$key])) {
                            $ok = false;
                            break;
                        }
                    }
                    if(!$ok) {
                        unset($this->partners[array_search($partner_id, $this->partners)]);
                    }
                };
            }
        }

        if(($this->service || $this->category) && !count($this->partners)) return $this->locations;

        $locations = DB::table('location_partner')
            ->select('location_id', DB::raw('count(*) as count'))
            ->leftJoin('partners', 'partners.id', '=', 'location_partner.partner_id')
            ->where('partners.status', 'Verified')
            ->whereIn('location_id', array_keys($this->locations))
            ->groupBy('location_id');


        if(count($this->partners)) {
            $locations = $locations->whereIn('partner_id', $this->partners);
        }

        foreach ($locations->pluck('count', 'location_id')->all() as $location_id => $partner_count) {
            $this->locations[$location_id]['partner_count'] = $partner_count;
        }

        return $this->locations;
    }

}