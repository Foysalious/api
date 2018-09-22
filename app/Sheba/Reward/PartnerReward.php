<?php

namespace Sheba\Reward;


use App\Models\Partner;
use App\Models\Reward;

class PartnerReward extends ShebaReward
{
    private $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
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
        foreach ($this->partner->categories as $category) {
            if (in_array($category->id, $reward_categories)) return true;
        }
        return false;
    }

    private function checkForPackage($package_constraints)
    {
        return in_array($this->partner->package_id, $package_constraints->pluck('constraint_id')->unique()->toArray());
    }

    public function upcoming()
    {
        $rewards = Reward::upcoming()->forPartner()->with('constraints')->get();
        $final = [];
        foreach ($rewards as $reward) {
            if ($this->isValidReward($reward)) array_push($final, $reward);
        }
        return $final;
    }

    public function running()
    {
        // TODO: Implement running() method.
    }
}