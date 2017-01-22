<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Partner;
use App\Models\PartnerOrder;
use Illuminate\Http\Request;

use App\Http\Requests;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::select('id', 'name', 'sub_domain', 'logo')->orderBy('name')->get();
        return response()->json(['partners' => $partners, 'code' => 200, 'msg' => 'successful']);
    }

    public function getPartnerServices($partner)
    {
        $partner = Partner::select('id', 'name', 'sub_domain', 'description', 'xp', 'logo')
            ->where('id', $partner)
            ->first();
        $review = $partner->reviews()->where('review', '<>', '')->count('review');
        $rating = $partner->reviews()->avg('rating');
        array_add($partner, 'review', $review);
        array_add($partner, 'rating', $rating);
        $partner_services = $partner->services()
            ->select('services.id', 'services.banner', 'services.category_id', 'name')
            ->where('is_verified', 1)
            ->get();
        foreach ($partner_services as $service) {
            array_forget($service, 'pivot');
            array_add($service, 'slug_service', str_slug($service->name, '-'));
            //review count of partner of this service
            $review = $service->reviews()->where([
                ['review', '<>', ''],
                ['partner_id', $partner->id]
            ])->count('review');
            //avg rating of the partner for this service
            $rating = $service->reviews()->where('partner_id', $partner->id)->avg('rating');
            array_add($service, 'review', $review);
            array_add($service, 'rating', $rating);
        }
        $partner_categories = $partner->categories()->select('categories.id', 'name')->get();
        foreach ($partner_categories as $category) {
            $service = $partner_services->where('category_id', $category->id);
            array_add($category, 'service', $service);
            array_forget($category, 'pivot');
        }
        if (count($partner)) {
            return response()->json([
                'partner' => $partner,
                'partner_categories' => $partner_categories,
                'msg' => 'successful',
                'code' => 200
            ]);
        }
    }

    public function insert()
    {
        $partner_orders = PartnerOrder::where('closed_at', null)->get();
        foreach ($partner_orders as $partner_order) {
            $partner_order->calculate();
            if ($partner_order->status == 'Closed') {
                $dates=$partner_order->jobs()->select('delivered_date')->get();
                $partner_order->closed_at=$dates->max()->delivered_date;
                $partner_order->update();
            }
        }
    }
}
