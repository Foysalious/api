<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Partner;
use Illuminate\Http\Request;

use App\Http\Requests;

class PartnerController extends Controller {
    public function getPartnerServices($partner)
    {
        $partner = Partner::select('id', 'name', 'sub_domain', 'description', 'xp', 'logo', 'rating')
            ->where('id', $partner)
            ->first();
        array_add($partner, 'review', 100);
        $partner_services = $partner->services()
            ->select('services.id', 'services.thumb', 'services.category_id', 'name')
            ->get();
        foreach ($partner_services as $service)
        {
            array_forget($service, 'pivot');
            array_add($service, 'slug_service', str_slug($service->name, '-'));
            array_add($service, 'review', 100);
            array_add($service, 'rating', 3.5);
        }
        $partner_categories = $partner->categories()->select('categories.id', 'name')->get();
        foreach ($partner_categories as $category)
        {
            $service = $partner_services->where('category_id', $category->id);
            array_add($category, 'service', $service);
            array_forget($category, 'pivot');
        }
        if (count($partner))
        {
            return response()->json([
                'partner' => $partner,
                'partner_categories' => $partner_categories,
                'msg' => 'successful',
                'code' => 200
            ]);
        }
    }
}
