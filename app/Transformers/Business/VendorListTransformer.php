<?php namespace App\Transformers\Business;

use App\Models\Partner;
use League\Fractal\TransformerAbstract;

class VendorListTransformer extends TransformerAbstract
{
    public function transform(Partner $partner)
    {
        $master_categories = collect();
        /** @var Partner $partner */
        $partner->categories->map(function ($category) use ($master_categories) {
            if (!$category->parent) return;
            $master_categories->push($category->parent);
        });
        $master_categories = $master_categories->unique()->pluck('name');
        return [
            "id" => $partner->id,
            "name" => $partner->name,
            "logo" => $partner->logo,
            "address" => $partner->address,
            "status" => $partner->is_active_for_b2b,
            "mobile" => $partner->getContactNumber(),
            'type' => $master_categories
        ];
    }
}