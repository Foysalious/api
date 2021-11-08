<?php namespace App\Http\Controllers\Pos;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function findById($partner, Request $request)
    {
        $partner = Partner::where('id', $partner)->select('id', 'name', 'logo', 'sub_domain')->first();
        $partner->banner=$this->getWebStoreBanner($partner);
        removeRelationsAndFields($partner,['webstore_banner']);
        if (!$partner) return http_response($request, null, 404);
        return http_response($request, $partner, 200, ['partner' => $partner]);
    }

    private function getWebStoreBanner($partner)
    {
        $web_store_banner = $partner->webstoreBanner;
        if (!$web_store_banner) return null;
        return [
            'image_link' => $web_store_banner->banner->image_link,
            'small_image_link' => $web_store_banner->banner->small_image_link,
            'title' => $web_store_banner->title,
            'description' => $web_store_banner->description,
            'is_published' => $web_store_banner->is_published
        ];
    }
}
