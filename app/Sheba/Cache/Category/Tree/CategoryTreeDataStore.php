<?php namespace App\Sheba\Cache\Category\Tree;

use Sheba\Dal\Category\Category;
use App\Models\Location;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\Category\Tree\CategoryTreeCacheRequest;
use Sheba\Cache\DataStoreObject;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;

class CategoryTreeDataStore implements DataStoreObject
{
    /** @var CategoryTreeCacheRequest */
    private $categoryTreeRequest;
    private $slugs;

    public function setCacheRequest(CacheRequest $request)
    {
        $this->categoryTreeRequest = $request;
        return $this;
    }

    public function generate()
    {
        if ($this->isInvalidLocation()) return null;

        $categories = $this->getCategories();
        $category_ids = $this->forgetMasterCategoryWithoutChildrenAndFlattenAllIds($categories);
        $this->setSlugs($category_ids);

        foreach ($categories as &$category) {
            $this->mapCategory($category);
            foreach ($category->children as &$child) {
                $this->mapCategory($child);
            }
        }
        return count($categories) > 0 ? ['categories' => $categories->values()->all()] : null;
    }

    private function isInvalidLocation()
    {
        $location = Location::where('id', $this->categoryTreeRequest->getLocationId())->published()->hasGeoInformation()->first();
        return !$location || !$location->hyperLocal;
    }

    private function getCategories()
    {
        return Category::publishedParentWithChildrenOnLocation($this->categoryTreeRequest->getLocationId())
            ->with(['children' => function ($q) {
                $q->select('id', 'name', 'thumb', 'parent_id', 'app_thumb', 'icon_png', 'icon_png_hover', 'icon_png_active', 'icon', 'icon_hover', 'is_auto_sp_enabled')
                    ->publishedWithServiceOnLocation($this->categoryTreeRequest->getLocationId())
                    ->orderBy('order');
            }])
            ->select('id', 'name', 'parent_id', 'icon_png', 'icon_png_hover', 'icon_png_active', 'app_thumb', 'app_banner', 'is_auto_sp_enabled')
            ->orderBy('order')->get();
    }

    private function forgetMasterCategoryWithoutChildrenAndFlattenAllIds(&$categories)
    {
        $ids = [];
        foreach ($categories as $key => $master_category) {
            if (count($master_category->children) == 0) {
                array_forget($categories, $key);
                continue;
            }
            array_push($ids, $master_category->id);
            foreach ($master_category->children as $category) {
                array_push($ids, $category->id);
            }
        }
        return $ids;
    }

    private function setSlugs($category_ids)
    {
        $this->slugs = UniversalSlugModel::where('sluggable_type', 'like', '%category')
            ->select('slug', 'sluggable_id')->whereIn('sluggable_id', $category_ids)->get()
            ->pluck('slug', 'sluggable_id')->toArray();
    }

    private function mapCategory(&$category)
    {
        $category['slug'] = isset($this->slugs[$category->id]) ? $this->slugs[$category->id] : null;

        if ($category['icon_png']) {
            $category['icon_png_sizes'] = getResizedUrls($category['icon_png'], 52, 52);
        }

        if ($category['icon_png_hover']) {
            $category['icon_png_hover_sizes'] = getResizedUrls($category['icon_png_hover'], 52, 52);
        }

        array_forget($category, 'parent_id');
    }
}