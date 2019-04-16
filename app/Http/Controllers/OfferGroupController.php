<?php namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\HomepageSetting;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\ScreenSettingElement;
use App\Sheba\Queries\Category\StartPrice;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfferGroupController extends Controller
{
    public function show($offer_group, Request $request)
    {
        $offer_group = [
            'id' => 1,
            "name" => "Flash Sales",
            "app_thumb" => "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/1/150.jpg",
            "offers" => [
                [
                    "id" => 10,
                    "target_type" => 'service_group',
                    "target_id" => 1,
                    "start_time" => "2019-04-16 14:19:11",
                    "end_time" => "2019-04-18 14:19:11",
                ],
                [
                    "id" => 12,
                    "target_type" => 'service_group',
                    "target_id" => 1,
                    "start_time" => "2019-04-16 14:19:11",
                    "end_time" => "2019-04-18 14:19:11",
                ],
            ]
        ];

        return api_response($request, $offer_group, 200, ['offer_group' => $offer_group]);
    }
}