<?php namespace App\Http\Controllers;

use App\Models\Category;
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
}
