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
        $this->partner->load(['categories' => function ($q) {
            $q->where('categories.publication_status', 1)->wherePivot('is_verified', 1);
        }]);
        $partner_categories = $this->partner->categories->pluck('id')->unique()->toArray();
        foreach ($category_constraints as $category_constraint) {
            if (in_array($category_constraint->constraint_id, $partner_categories)) return true;
        }
        return false;
    }

    private function checkForPackage($package_constraints)
    {
        foreach ($package_constraints as $package_constraint) {
            if ($this->partner->package_id == $package_constraint->constraint_id) return true;
        }
        return false;
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