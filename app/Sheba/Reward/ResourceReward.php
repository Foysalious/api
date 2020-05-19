<?php namespace Sheba\Reward;

use App\Models\Resource;
use App\Models\Reward;

class ResourceReward extends ShebaReward
{
    private $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    private function isValidReward($reward)
    {
        $category_constraints = $reward->constraints->where('constraint_type', constants('REWARD_CONSTRAINTS')['category']);
        $category_pass = $category_constraints->count() == 0;

        $package_constraints = $reward->constraints->where('constraint_type', constants('REWARD_CONSTRAINTS')['partner_package']);
        $package_pass = $package_constraints->count() == 0;

        if (!$category_pass) $category_pass = $this->checkForCategory($category_constraints);
        if (!$package_pass) $package_pass = $this->checkForPackage($package_constraints);
        return $category_pass && $package_pass;
    }

    private function checkForCategory($category_constraints)
    {
        $reward_categories = $category_constraints->pluck('constraint_id')->unique()->toArray();
        foreach ($this->resource->categories as $category) {
            if (in_array($category->id, $reward_categories)) return true;
        }
        return false;
    }

    private function checkForPackage($package_constraints)
    {
        return in_array($this->resource->package_id, $package_constraints->pluck('constraint_id')->unique()->toArray());
    }

    public function running()
    {
        // TODO: Implement running() method.
    }

    public function upcoming()
    {
        $rewards = Reward::upcoming()->forResource()->with('constraints')->get();
        $final = [];
        foreach ($rewards as $reward) {
            if ($this->isValidReward($reward)) array_push($final, $reward);
        }
        return $final;
    }
}