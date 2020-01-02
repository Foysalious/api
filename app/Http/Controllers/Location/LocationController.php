<?php namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocationController extends Controller
{

    public function index(Request $request)
    {
        try {
            $cities = [
                [
                    'id' => 1,
                    'name' => 'Dhaka',
                    'image' => "https://cdn-shebadev.s3.ap-south-1.amazonaws.com/sheba_xyz/jpg/dhaka.jpg",
                    'center' => [
                        'lat' => 23.788994076131,
                        'lng' => 90.410852011945
                    ]
                ],
                [
                    'id' => 2,
                    'name' => 'Chittagong',
                    'image' => "https://cdn-shebadev.s3.ap-south-1.amazonaws.com/sheba_xyz/jpg/chittagong.jpg",
                    'center' => [
                        'lat' => 22.35585575222634,
                        'lng' => 91.85625492089844
                    ]
                ]
            ];
            return api_response($request, $cities, 200, ['cities' => $cities]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

}