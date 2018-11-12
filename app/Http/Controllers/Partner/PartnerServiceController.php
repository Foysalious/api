<?php namespace App\Http\Controllers\Partner;

use App\Models\PartnerService;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class PartnerServiceController extends Controller
{
    public function index(Request $request)
    {
        $partner_services = PartnerService::with(['service.category.parent'])->where('partner_id', $request->partner->id)->published()->withCount(['pricesUpdates' => function ($query) {
            $query->status(constants('PARTNER_SERVICE_UPDATE_STATUS')['Pending']);
        }])->get();
        $master_categories = collect();
        foreach ($partner_services as $partner_service) {
            if (!$partner_service->service->publication_status && !$partner_service->service->is_published_for_backend) continue;

            $master_category = $partner_service->service->category->parent;
            $master_category_in_collection = $master_categories->where('id', $master_category->id)->first();
            if (!$master_category_in_collection) {
                $master_categories->push([
                    'id' => $master_category->id,
                    'name' => $master_category->name,
                    'sub_categories' => collect([[
                        'id' => $partner_service->service->category->id,
                        'name' => $partner_service->service->category->name,
                        'services' => collect([[
                            'id' => $partner_service->id,
                            'name' => $partner_service->service->name,
                            'has_update_request' => $partner_service->prices_updates_count,
                            'thumb' => $partner_service->service->app_thumb
                        ]])
                    ]])
                ]);
            } else {
                $sub_category_in_collection = $master_category_in_collection['sub_categories']->where('id', $partner_service->service->category->id)->first();
                if (!$sub_category_in_collection) {
                    $master_category_in_collection['sub_categories']->push([
                        'id' => $partner_service->service->category->id,
                        'name' => $partner_service->service->category->name,
                        'services' => collect([[
                            'id' => $partner_service->id,
                            'name' => $partner_service->service->name,
                            'has_update_request' => $partner_service->prices_updates_count,
                            'thumb' => $partner_service->service->app_thumb
                        ]])
                    ]);
                } else {
                    $sub_category_in_collection['services']->push([
                        'id' => $partner_service->id,
                        'name' => $partner_service->service->name,
                        'has_update_request' => $partner_service->prices_updates_count,
                        'thumb' => $partner_service->service->app_thumb
                    ]);
                }
            }
        }
        return api_response($request, $partner_services, 200, ['master_categories' => $master_categories]);
    }
}
