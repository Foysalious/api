<?php namespace Sheba\Availability;


use App\Models\Location;

class ServiceAvailability
{
    private $locationId;
    /** @var array */
    private $ids;
    private $type;

    /**
     * @param $location_id
     * @return ServiceAvailability
     */
    public function setLocation($location_id)
    {
        $this->locationId = $location_id;
        return $this;
    }

    /**
     * @param array $ids
     * @return ServiceAvailability
     */
    public function setIds($ids)
    {
        $this->ids = $ids;
        return $this;
    }

    /**
     * @param mixed $type
     * @return ServiceAvailability
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getAvailability()
    {
        $model = "App\\Models\\" . ucwords($this->type);
        $models = $model::whereIn('id', $this->ids)->whereHas('locations', function ($q) {
            $q->where('locations.id', $this->location->id);
        });
        $models = $models->get();
        if ($this->type == 'category') {
            $models = $models->load(['children' => function ($q) {
                $q->whereHas('locations', function ($q) {
                    $q->where('locations.id', $this->location->id);
                });
            }, 'services' => function ($q) {
                $q->whereHas('locations', function ($q) {
                    $q->where('locations.id', $this->location->id);
                });
            }]);
            $models = $models->filter(function ($category) {
                $children = $category->isParent() ? $category->children : $category->services;
//                foreach ($children as $child) {
//                    if (in_array($this->location->id, $child->locations->pluck('id')->toArray())) {
//                        return true;
//                    }
//                }
                return false;
            });
        }
        foreach ($this->ids as $id) {
            array_push($final_services, ['id' => (int)$id, 'is_available' => $models->where('id', $id)->first() ? 1 : 0]);
        }
        return $final_services;
    }

}