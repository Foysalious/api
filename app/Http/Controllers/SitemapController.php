<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Info\CategoryCacheRequest;

class SitemapController extends Controller
{
    public function index(Request $request, CacheAside $cacheAside, CategoryCacheRequest $cacheRequest)
    {

        $data = [
            [
                "id" => 6,
                "name" => "Automobile Maintenance",
                "slug" => "automobile-maintenance",
                "secondary_categories" => [
                    [
                        "id" => 43,
                        "name" => "Automobile Basic Service",
                        "slug" => "automobile-basic-service",
                        "services" => [
                            [
                                "id" => 81,
                                "name" => "Car Wash",
                                "slug" => "car-wash"
                            ],
                            [
                                "id" => 105,
                                "name" => "Car Servicing - General",
                                "slug" => "car-servicing-general"
                            ],
                            [
                                "id" => 109,
                                "name" => "Engine Replacement",
                                "slug" => "engine-replacement"
                            ],
                            [
                                "id" => 113,
                                "name" => "Engine OverHauling",
                                "slug" => "engine-overhauling"
                            ]
                        ]
                    ],
                    [
                        "id" => 44,
                        "name" => "Engine Electrical & Computerized Checkup",
                        "slug" => "engine-electrical-computerized-checkup",
                        "services" => [
                            [
                                "id" => 81,
                                "name" => "Car Wash",
                                "slug" => "car-wash"
                            ],
                            [
                                "id" => 105,
                                "name" => "Car Servicing - General",
                                "slug" => "car-servicing-general"
                            ],
                            [
                                "id" => 109,
                                "name" => "Engine Replacement",
                                "slug" => "engine-replacement"
                            ],
                            [
                                "id" => 113,
                                "name" => "Engine OverHauling",
                                "slug" => "engine-overhauling"
                            ],
                            [
                                "id" => 109,
                                "name" => "Engine Replacement",
                                "slug" => "engine-replacement"
                            ],
                            [
                                "id" => 113,
                                "name" => "Engine OverHauling",
                                "slug" => "engine-overhauling"
                            ]
                        ]
                    ],
                    [
                        "id" => 46,
                        "name" => "Fuel System",
                        "slug" => "fuel-system",
                        "services" => [
                            [
                                "id" => 81,
                                "name" => "Car Wash",
                                "slug" => "car-wash"
                            ],
                            [
                                "id" => 105,
                                "name" => "Car Servicing - General",
                                "slug" => "car-servicing-general"
                            ],
                            [
                                "id" => 109,
                                "name" => "Engine Replacement",
                                "slug" => "engine-replacement"
                            ],
                            [
                                "id" => 113,
                                "name" => "Engine OverHauling",
                                "slug" => "engine-overhauling"
                            ],
                            [
                                "id" => 109,
                                "name" => "Engine Replacement",
                                "slug" => "engine-replacement"
                            ],
                            [
                                "id" => 113,
                                "name" => "Engine OverHauling",
                                "slug" => "engine-overhauling"
                            ]
                        ]
                    ]
                ],
            ],
            [
                "id" => 4,
                "name" => "Pack & Shift",
                "slug" => "buy-bed-with-free-assemble",
                "secondary_categories" => [
                    [
                        "id" => 35,
                        "name" => "Manpower",
                        "slug" => "manpower",
                        "services" => [
                            [
                                "id" => 81,
                                "name" => "Car Wash",
                                "slug" => "car-wash"
                            ],
                            [
                                "id" => 105,
                                "name" => "Car Servicing - General",
                                "slug" => "car-servicing-general"
                            ],
                            [
                                "id" => 109,
                                "name" => "Engine Replacement",
                                "slug" => "engine-replacement"
                            ],
                            [
                                "id" => 113,
                                "name" => "Engine OverHauling",
                                "slug" => "engine-overhauling"
                            ]
                        ]
                    ],
                    [
                        "id" => 98,
                        "name" => "Pack & Shift Packages",
                        "slug" => null,
                        "services" => [
                            [
                                "id" => 81,
                                "name" => "Car Wash",
                                "slug" => "car-wash"
                            ],
                            [
                                "id" => 105,
                                "name" => "Car Servicing - General",
                                "slug" => "car-servicing-general"
                            ],
                            [
                                "id" => 109,
                                "name" => "Engine Replacement",
                                "slug" => "engine-replacement"
                            ],
                            [
                                "id" => 113,
                                "name" => "Engine OverHauling",
                                "slug" => "engine-overhauling"
                            ],
                            [
                                "id" => 109,
                                "name" => "Engine Replacement",
                                "slug" => "engine-replacement"
                            ],
                            [
                                "id" => 113,
                                "name" => "Engine OverHauling",
                                "slug" => "engine-overhauling"
                            ]
                        ]
                    ]
                ],
            ]
        ];
        if (!$data) return api_response($request, null, 404);
        return api_response($request, $data, 200, ['master_categories' => $data]);
    }
}