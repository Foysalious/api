<?php namespace App\Http\Controllers\Partner;

use App\Models\Category;
use App\Models\PartnerService;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class PartnerServiceController extends Controller
{
    public function index(Request $request)
    {
        $partner_services = [
            [
                'id' => 236,
                'name' => 'Trip & Travels',
                'sub_categories' => [
                    [
                        'id' => 38,
                        'name' => 'Airport Pick or Drop',
                        'services' => [
                            [
                                'id' => 117,
                                'name' => 'Full Day Car Rental - Within Dhaka ( 10 Hours )',
                                'has_update_request' => 1,
                                'thumb' => "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/service_resized/117/thumb100X100.jpg"
                            ],
                            [
                                'id' => 118,
                                'name' => 'Full Day Car Rental - Within Dhaka ( 10 Hours )2',
                                'has_update_request' => 0,
                                'thumb' => "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/service_resized/117/thumb100X100.jpg"
                            ]
                        ]
                    ],
                    [
                        'id' => 39,
                        'name' => 'Day Long Tour Near Dhaka',
                        'services' => [
                            [
                                'id' => 6256,
                                'name' => 'Visit Fantasy Kingdom',
                                'has_update_request' => 0,
                                'thumb' => "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/service_resized/117/thumb100X100.jpg"
                            ],
                            [
                                'id' => 6257,
                                'name' => 'Visit Fantasy Kingdom2',
                                'has_update_request' => 1,
                                'thumb' => "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/service_resized/117/thumb100X100.jpg"
                            ],
                        ]
                    ]
                ]
            ]
        ];
        return api_response($request, $partner_services, 200, ['master_categories' => $partner_services]);
//        $partner_services = PartnerService::with(['service.category.parent'])->where('partner_id', $request->partner->id)->published()->withCount(['pricesUpdates' => function ($query) {
//            $query->status(constants('PARTNER_SERVICE_UPDATE_STATUS')['Pending']);
//        }])->get();
//        return $partner_services;
    }
}
