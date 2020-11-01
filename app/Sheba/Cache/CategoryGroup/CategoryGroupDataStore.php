<?php namespace Sheba\Cache\CategoryGroup;

use App\Models\CategoryGroup;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;

class CategoryGroupDataStore implements DataStoreObject
{
    /** @var CategoryGroupCacheRequest */
    private $categoryGroupCacheRequest;


    public function setCacheRequest(CacheRequest $request)
    {
        $this->categoryGroupCacheRequest = $request;
        return $request;
    }

    /**
     * @inheritDoc
     */
    public function generate()
    {
        $category_group = CategoryGroup::whereHas('categories', function ($q) {
            $q->published()->whereHas('services', function ($q) {
                $q->published()->whereHas('locations', function ($q) {
                    $q->where('locations.id', $this->categoryGroupCacheRequest->getLocationId());
                });
            })->whereHas('locations', function ($q) {
                $q->where('locations.id', $this->categoryGroupCacheRequest->getLocationId());
            });
        })->where('id', $this->categoryGroupCacheRequest->getCategoryGroupId())->with(['categories' => function ($q) {
            $q->select('id', 'name', 'thumb', 'app_thumb', 'banner', 'app_banner', 'icon', 'icon_png', 'parent_id');
        }])->select('id', 'name')->first();

        if (!$category_group) return null;
        return [
            'category' => [
                'id' => $category_group->id,
                'name' => $category_group->name,
                'secondaries' => $category_group->categories->map(function ($category) {
                    $category['slug'] = $category->getSlug();
                    removeRelationsAndFields($category);
                    return $category;
                })
            ]
        ];
    }
}