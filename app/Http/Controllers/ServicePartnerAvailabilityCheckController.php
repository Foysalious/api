<?php namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Partner;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Requests;

class ServicePartnerAvailabilityCheckController extends Controller
{
    public function publicationStatus(Request $request, Category $category, Service $service)
    {
        $publication_status = [];
        $publication_status['category'] = $category->publication_status;
        $publication_status['service'] = $service->publication_status;
        return api_response($request, $publication_status, 200, ['publication_status' => $publication_status]);
    }

    public function partnerAvailabilityStatus(Request $request, Partner $partner)
    {
        $partner_service = $partner->services()->where('service_id', $request->service)->select('is_published', 'is_verified')->first();
        $category_partner = $partner->categories()->where('category_id', $request->category)->select('is_verified')->first();
        $partner_status = [];
        $partner_status['partner_service_published'] = $partner_service->is_published;
        $partner_status['partner_service_verified'] = $partner_service->is_verified;
        $partner_status['wallet_amount'] = $partner->wallet;
        $partner_status['subscription'] = $partner->subscription->name;
        return api_response($request, $partner_status, 200, ['publication_status' => $partner_status]);
    }
}
