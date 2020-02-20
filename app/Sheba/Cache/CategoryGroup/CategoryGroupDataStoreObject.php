<?php namespace Sheba\Cache\CategoryGroup;

use App\Models\CategoryGroup;
use Sheba\Cache\DataStoreObject;

class CategoryGroupDataStoreObject extends DataStoreObject
{
    private $categoryGroupId;
    private $locationId;

    public function setLocationId($location_id)
    {
        $this->locationId = $location_id;
        return $this;
    }

    public function setCategoryGroupId($categoryGroupId)
    {
        $this->categoryGroupId = $categoryGroupId;
        return $this;
    }

    public function generateData()
    {
        $category_group = CategoryGroup::whereHas('categories', function ($q) {
            $q->published()->whereHas('services', function ($q) {
                $q->published()->whereHas('locations', function ($q) {
                    $q->where('locations.id', $this->locationId);
                });
            })->whereHas('locations', function ($q) {
                $q->where('locations.id', $this->locationId);
            });
        })->where('id', $this->categoryGroupId)->with(['categories' => function ($q) {
            $q->select('id', 'name', 'thumb', 'app_thumb', 'banner', 'app_banner', 'icon', 'icon_png', 'parent_id');
        }])->select('id', 'name')->first();

        if (!$category_group) $this->setData(['code' => 404, 'message' => 'Not found']);
        else {
            $this->setData([
                'category' => [
                    'name' => $category_group->name,
                    'secondaries' => $category_group->categories->map(function ($category) {
                        $category['slug'] = $category->getSlug();
                        removeRelationsAndFields($category);
                        return $category;
                    })
                ]
            ]);
        }
    }
}