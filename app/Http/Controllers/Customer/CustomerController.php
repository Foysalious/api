<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function getOrderAgain($customer, Request $request)
    {
        $info = "[{\"id\":4,\"name\":\"Daily lunch meal\",\"thumb\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/services_images/thumbs/1574313308_ac_diagnosis_filter_cleaning_basic_minor_services_2_ac_@799.jpeg\",\"rating\":4,\"option\":[0],\"partner\":{\"id\":233,\"logo\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1519727255_express_solution.jpg\"},\"option_prices\":[{\"option\":[0],\"price\":100}],\"type\":\"Options\",\"discount\":{\"value\":20,\"is_percentage\":0,\"cap\":0},\"delivery_charge\":50,\"delivery_discount\":{\"value\":30,\"is_percentage\":1,\"cap\":10,\"min_order_amount\":0}},{\"id\":5,\"name\":\"Facial\",\"thumb\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/services_images/thumbs/1574313308_ac_diagnosis_filter_cleaning_basic_minor_services_2_ac_@799.jpeg\",\"partner\":{\"id\":233,\"logo\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/1519727255_express_solution.jpg\"},\"rating\":5,\"option\":[],\"fixed_price\":200,\"type\":\"Fixed\",\"discount\":null,\"delivery_charge\":0,\"delivery_discount\":null}]";
        return api_response($request, $info, 200, ['services' => json_decode($info)]);
        $customer = $request->customer;
        $reviews = Review::where([['customer_id', $customer->id], ['rating', '>=', 4]])->orderBy('id', 'desc');
        if ($request->has('category_id')) $reviews = $reviews->where('category_id', $request->category_id);
        $reviews = $reviews->get();

        dd($reviews);
    }
}