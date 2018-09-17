<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RewardShopController extends Controller
{
    public function index(Request $request)
    {
        try{
            $products = collect([
                [
                    'id' => 1,
                    'name' => 'Visiting Card',
                    'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/services_images/thumbs/1482401445_hire_electrician.png',
                    'required_point' => 1000
                ],
                [
                    'id' => 2,
                    'name' => 'T-Shirt',
                    'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/services_images/thumbs/1482401445_hire_electrician.png',
                    'required_point' => 5000
                ],
                [
                    'id' => 3,
                    'name' => 'AC',
                    'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/services_images/thumbs/1482401445_hire_electrician.png',
                    'required_point' => 10000
                ],
                [
                    'id' => 3,
                    'name' => 'Bike',
                    'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/services_images/thumbs/1482401445_hire_electrician.png',
                    'required_point' => 50000
                ]
            ]);
            return api_response($request, $products, 200, ['products' => $products]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
