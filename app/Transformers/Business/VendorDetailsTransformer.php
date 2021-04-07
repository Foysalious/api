<?php namespace App\Transformers\Business;

use App\Models\Partner;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class VendorDetailsTransformer extends TransformerAbstract
{
    public function transform(Partner $partner)
    {
        $basic_informations = $partner->basicInformations;
        $resources = $partner->resources->count();
        $type = $partner->businesses->pluck('type')->unique();

        $master_categories = collect();
        $partner->categories->map(function ($category) use ($master_categories) {
            $parent_category = $category->parent()->select('id', 'name')->first();
            $master_categories->push($parent_category);
        });
        $master_categories = $master_categories->unique()->pluck('name');

        return [
            "id" => $partner->id,
            "name" => $partner->name,
            "logo" => $partner->logo,
            "mobile" => $partner->getContactNumber(),
            "email" => $partner->email,
            "address" => $partner->address,
            "status" => $partner->is_active_for_b2b,
            "company_type" => $type,
            "service_type" => $master_categories,
            "no_of_resource" => $resources,
            "trade_license" => $basic_informations->trade_license,
            "trade_license_attachment" => $basic_informations->trade_license_attachment,
            "vat_registration_number" => $basic_informations->vat_registration_number,
            "vat_registration_attachment" => $basic_informations->vat_registration_attachment,
            "establishment_year" => $basic_informations->establishment_year ? Carbon::parse($basic_informations->establishment_year)->format('M, Y') : null
        ];
    }
}
