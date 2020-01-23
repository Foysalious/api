<?php namespace Sheba\Checkout\Partners;


use App\Models\Partner;
use App\Sheba\Checkout\PartnerList;
use DB;
use Sheba\Dal\PartnerLocation\PartnerLocation;
use Sheba\Dal\PartnerLocation\PartnerLocationRepository;

class LitePartnerList extends PartnerList
{
    private $limit;

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function find($partner_id = null)
    {
        $this->setPartner($partner_id);
        $this->partners = $this->findPartnersByService()->reject(function ($partner) {
            return $partner->geo_informations == null;
        });
        $this->filterPartnerByRadius();
        $this->partners->load(['services' => function ($q) {
            $q->whereIn('service_id', $this->partnerListRequest->selectedServiceIds);
        }, 'categories' => function ($q) {
            $q->where('categories.id', $this->partnerListRequest->selectedCategory->id);
        }]);
        $this->filterByOption();
        $this->partners = $this->partners->sortBy('distance');
    }

    protected function findPartnersByService()
    {
        $category_ids = [$this->partnerListRequest->selectedCategory->id];
        $query = Partner::WhereHas('categories', function ($q) use ($category_ids) {
            $q->whereIn('categories.id', $category_ids);
        })->whereHas('services', function ($query) {
            $query->select(DB::raw('count(*) as c'))->whereIn('services.id', $this->partnerListRequest->selectedServiceIds)
                ->groupBy('partner_id')->havingRaw('c=' . count($this->partnerListRequest->selectedServiceIds));
        })->where([['package_id', 1], ['moderator_id', '<>', null], ['moderation_status', 'approved']])
            ->select('partners.id', 'partners.geo_informations', 'partners.status', 'partners.address', 'partners.name', 'partners.sub_domain', 'partners.logo');
        if ($this->partner) $query = $query->where('partners.id', $this->partner->id);
        return $query->get();
    }

    private function getNearByPartnerIds()
    {
        $nearByPartners = (new PartnerLocationRepository(new PartnerLocation()))->findNearByPartners($this->partnerListRequest->lat, $this->partnerListRequest->lng);
        return $nearByPartners->pluck('partner_id')->unique()->toArray();
    }

    public function addInfo()
    {
        $this->partners = $this->partners->take($this->limit);
        $this->partners->load(['resources' => function ($q) {
            $q->select('resources.id', 'profile_id')->with(['profile' => function ($q) {
                $q->select('profiles.id', 'mobile');
            }]);
        }]);
        foreach ($this->partners as $partner) {
            $partner['contact_no'] = $partner->getContactNumber();
            $partner['distance_km'] = round($partner['distance'] / 1000, 2);
            $partner['status'] = "Not Verified";
        }
    }


}
