<?php namespace App\Http\Controllers\Service;


use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\LocationService;
use App\Models\Service;
use App\Transformers\Category\CategoryTransformer;
use App\Transformers\Service\ServiceTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;

class ServiceController extends Controller
{
    /**
     * @todo Code refactor
     */
    public function show($service, Request $request, ServiceTransformer $service_transformer, PriceCalculation $price_calculation, UpsellCalculation $upsell_calculation, DeliveryCharge $delivery_charge, JobDiscountHandler $job_discount_handler)
    {
        $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric']);
        $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
        if (!$hyperLocation) return api_response($request, null, 404);
        /** @var Service $service */
        $service = Service::find($service);
        if (!$service) return api_response($request, null, 404);
        /** @var Location $location */
        $location = $hyperLocation->location;
        $location_service = LocationService::where('location_id', $location->id)->where('service_id', $service->id)->first();
        $fractal = new Manager();
        $service_transformer->setLocationService($location_service);
        $resource = new Item($service, $service_transformer);
        $data = $fractal->createData($resource)->toArray()['data'];
        return api_response($request, $data, 200, ['service' => $data]);
//        $service = Service::select('id', 'category_id', 'name', 'thumb', 'banner', 'app_thumb', 'variable_type', 'variables')->where('id', $service)->first();
        $location_service = LocationService::where('location_id', $location->id)->where('service_id', $service->id)->first();
        /** @var ServiceDiscount $discount */
        $discount = $location_service->discounts()->running()->first();

        $prices = json_decode($location_service->prices);
        $price_calculation->setLocationService($location_service);
        $upsell_calculation->setLocationService($location_service);

        if ($service->variable_type == 'Options') {
            $service['option_prices'] = $this->formatOptionWithPrice($price_calculation, $prices, $upsell_calculation, $location_service);
        } else {
            $service['fixed_price'] = $price_calculation->getUnitPrice();
            $service['fixed_upsell_price'] = $upsell_calculation->getAllUpsellWithMinMaxQuantity();
        }

        $service['discount'] = $discount ? [
            'value' => (double)$discount->amount,
            'is_percentage' => $discount->isPercentage(),
            'cap' => (double)$discount->cap
        ] : null;
        $category = $service->category;
        $cross_sale_service = $category->crossSaleService;
        $cross_sale = $cross_sale_service ? [
            'title' => $cross_sale_service->title,
            'description' => $cross_sale_service->description,
            'icon' => $cross_sale_service->icon,
            'category_id' => $cross_sale_service->category_id,
            'service_id' => $cross_sale_service->service_id
        ] : null;

        $delivery_charge = $delivery_charge->setCategory($category)->get();
        $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($delivery_charge);
        $job_discount_handler->setType(DiscountTypes::DELIVERY)->setCategory($category)->setCheckingParams($discount_checking_params)->calculate();
        /** @var Discount $delivery_discount */
        $delivery_discount = $job_discount_handler->getDiscount();
        $delivery_discount = $delivery_discount ? [
            'value' => (double)$delivery_discount->amount,
            'is_percentage' => $delivery_discount->is_percentage,
            'cap' => (double)$delivery_discount->cap,
            'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
        ] : null;
        $info = "{\"id\":1043,\"name\":\"Ac Service Repair\",\"avg_rating\":4.5,\"total_ratings\":500,\"total_services\":200,\"total_resources\":40,\"total_served_orders\":1000,\"banner\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\",\"usp\":[\"We have more than 300 services\",\"Verified experts all arround the country\",\"24x7 Support - every day, every hour\"],\"overview\":{\"title\":\"Service Overview\",\"components\":[{\"title\":\"What’s Included\",\"short_description\":\"Things that are included - \",\"description\":{\"type\":\"ol\",\"value\":[\"Order has been placed\",\"Order has been confirmed.\"]}},{\"title\":\"What’s Excluded\",\"short_description\":\"Things that are excluded -  \",\"description\":{\"type\":\"ul\",\"value\":[\"Order has been placed\",\"Order has been confirmed.\"]}}]},\"partnership\":{\"title\":\"Partnership\",\"short_description\":\"Your happiness is our goal. If you’re not happy, we’ll work to make it right. Our friendly customer service agents are available 24 hours a day, 7 days a week.\",\"images\":[\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\",\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\",\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\",\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\",\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\"]},\"faq\":[{\"question\":\"Your happiness is our goal. If you’re not happy?\",\"answer\":\"Your happiness is our goal. If you’re not happy, we’ll work to make it right. Our friendly customer service agents are available 24 hours a day, 7 days a week.\"},{\"question\":\"Your happiness is our goal. If you’re not happy?\",\"answer\":\"Your happiness is our goal. If you’re not happy, we’ll work to make it right. Our friendly customer service agents are available 24 hours a day, 7 days a week.\"},{\"question\":\"Your happiness is our goal. If you’re not happy?\",\"answer\":\"Your happiness is our goal. If you’re not happy, we’ll work to make it right. Our friendly customer service agents are available 24 hours a day, 7 days a week.\"}],\"gallery\":[{\"id\":1,\"image\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\"},{\"id\":1,\"image\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\"},{\"id\":1,\"image\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\"},{\"id\":1,\"image\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\"}],\"blog\":[{\"id\":1,\"title\":\"Summer Facial\",\"short_description\":\"Your happiness is our goal. If you’re Our friendly customer….\",\"image\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\",\"target_link\":\"https://www.sheba.xyz/\"},{\"id\":1,\"title\":\"Summer Facial\",\"short_description\":\"Your happiness is our goal. If you’re Our friendly customer….\",\"image\":\"https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1495262683_home_appliances_.png\",\"target_link\":\"https://www.sheba.xyz/\"}],\"details\":\"At Sheba.xyz you can hire expert AC repair service near you. Our professional Service Providers will give you the best AC repair service. From general inspection to changing AC parts you can avail every AC related service within few moment. <br><h3><br>We offer:</h3><ul><li>AC Checkup</li><li>AC Basic Servicing</li><li>AC Gas Charge</li><li>AC Master Service</li><li>AC Water Drop Solution</li><li>AC Installation &nbsp;</li><li>AC Shifting Service</li><li>AC Compressor Fitting</li><li>AC Dismantling</li><li>AC Capacitor Replacement </li><li>AC Service Repairing &nbsp;&nbsp;</li></ul><b>&nbsp;What’s included:</b><ul><li>Service Charge</li><li>7 Days service warranty &nbsp;&nbsp;</li></ul><b>&nbsp;What’s excluded:</b> <ul><li>Price of Materials or Parts </li><li>Transportation cost for carrying new materials/parts</li><li>Warranty given by manufacturer </li></ul>&nbsp;<b>Pricing</b><ul><li>You only have to pay the service charge including materials/parts cost if taken</li><li>Vising cost will have to pay if no service is availed</li></ul> <b>Payment:</b><br><br>After service completion you will receive a text message on your mobile from Sheba.xyz then you have to pay through Online or Cash on Delivery.&nbsp;<br><br><b>Liability:<br></b><br>Sheba.xyz will not be liable for any pre-existing issues or potential risks reported by the technician but not handled due to customer’s refusal to repair. <br><br><b>Night Service:</b>&nbsp;10pm to 8am<ul><li>Night service starts from 10:00 pm to 8:00 am</li><li>Minimum 4 Hours Lead time after service booking</li><li>In excess of BDT 500 will be charged as Emergency Support Service Charge</li><li>If for any reason the customer refuses to take service after order confirmation, only the Emergency Support Service Charge will be applicable</li><li>Sheba.xyz will not liable for any direct or incidental loss/damage of client’s property or personal security during availing the service, caused by accident, theft, burglary or any other type of incidental damages.</li><li>Client is singularly responsible for monitoring, using and supervising the activities provided by Service providers.</li><li>By availing the service, clients automatically discharge Sheba.xyz from any claims or legal/moral liabilities other than stated in the Terms of service specified by Sheba.xyz.</li></ul><b>AC Checkup Service</b>: AC Checkup service offers only diagnosis of your Air Conditioner by an expert technician who perform initial tests for problem identification.&nbsp;<br><br><b>AC Basic Servicing</b>:&nbsp;AC Basic service offers primary diagnosis, filter cleaning, test and identify problems by an expert AC technician. <br><br><b>AC Gas Charge</b>: This service offers performance checkup and post gas refill. If there is a leakage; most of the time AC can be fixed onsite but sometimes it might take longer time. For that you have to wait for 1 or 2 days.&nbsp;<br><br><b>AC Master Service</b>: AC Master Service offers &nbsp;detail cleaning of indoor and outdoor unit including minor problem fixing (excluding materials and parts). Service charge varies on your AC amount, height, weight and difficulties.&nbsp;<br><br><b>AC Water Drop Solution</b>: This service offers identification of the source of dripping water from you AC and fixation water drainage system accordingly. Any additional materials/parts will be charged separately.<br><br><b>AC Shifting Service</b>: This service is to shift your AC unit from one place or floor to the loading truck. Only service charge is applicable for this service. Service charge varies on your AC amount, height, weight and difficulties.&nbsp;<br><br><b>AC Compressor Fitting With Gas Charge</b>:&nbsp;This service offers old Compressor removal and new Compressor installation. Compressor price and warranty differs as per manufacturer.<br><br><b>AC Dismantling</b>:&nbsp;This service offers dismantling AC from home or workplace and disconnecting all the electrical wiring from the AC unit.&nbsp;<br><br><b>AC Capacitor Replacement</b>:&nbsp;This service offers replacing AC capacitor with a new one. Capacitor price and warranty differs as per manufacturer.&nbsp;<br><br><b>AC Circuit Repairing</b>:&nbsp;This service offers repairing for the circuits of you AC. Circuit box price and warranty differs as per manufacturer.<br><br><h3>Why Us?<br></h3><br><b>Hassle Free:</b> Ordering AC repair service from us is simple and easy. You can hire expert Service Providers from us hassle free to carry your AC here and there. Our Service Provider will come to your doorstep for you. &nbsp;<br><br><b>Budget Friendly</b>: You can hire Professional AC repairing service in the same budget or less than any other local services near near you. Our Service Providers will provide expert AC technicians to inspect &nbsp;problems and fix them. <br><br><b>Well-trained Professionals</b>: Our professional Service Providers have discreet and skilled AC repairing technicians. Their backgrounds are thoroughly checked in details. Safety Assurance: Our service providers offer a safe AC repairing service for you. This means they will handle repairing with care.<br><br><h3>FAQ (Frequently Asked Questions)</h3>&nbsp;<br><br><b>Do I have to pay any charge if I don’t take any service?</b>&nbsp;<br><br>If you don’t avail any services for your AC after our Service Provider send a technician at your doorstep then you only have to pay the visiting charge which is BDT 100. &nbsp;<br><br><b>Do I have to pay advance money before availing your service?<br></b><br>Of course not! After service completion you will receive a text on your mobile from Sheba.xyz then you have to pay through Online or Cash on Delivery. &nbsp;<br><br><b>Is this only for household AC?<br></b><br>Definitely not! As long as you want to avail this service for your AC then you can order for your office Air Conditioners too.<br><br><b>What if they damage my AC?<br></b><br>Our professional Service Providers have expert and skilled AC technicians. If they occur any damages to your AC during repairing you will get proper compensation after proper investigation. However, your complaint for any pre-damaged problems will not be considered.&nbsp;<br><br><b>Do you give Materials/Parts warranty?<br></b><br>No. We do not manufacture AC parts by ourselves. So, the warranty differs as per manufacturer. However, we can give you 7 days free service warranty. <br><br><b>Can I buy AC materials/parts by myself and ask your technician to use them?<br></b><br>Certainly. You can buy necessary and required materials/parts by yourself. But any operational dysfunctionality won’t be held responsible by our Service Providers and Sheba.xyz itself.<br><b><br>About Sheba.xyz's&nbsp;AC Repairing Service:</b><br><br>Sheba.xyz is the largest marketplace in Bangladesh where we serve you with every possible services. AC Repairing service is one of our services to repair your all types of AC related problems. We deliver expert and AC repairing services with integrity from our professional Service Providers.&nbsp;<span><br><br></span><div></div>\"}";
        $info = json_decode($info, true);
        removeRelationsAndFields($service);
        $service['category'] = [
            'cross_sale' => $cross_sale,
            'delivery_discount' => $delivery_discount,
            'delivery_charge' => $delivery_charge,
            'name' => $category->name,
            'slug' => $category->getSlug()
        ];
        $info = array_merge($info, $service->toArray());
        $info['questions'] = null;
        if ($service->isOptions()) {
            $options = (json_decode($info['variables']))->options;
            foreach ($options as &$option) {
                $option->answers = explode(',', $option->answers);
                $option->contents = [
                    'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/74/600.jpg',
                    'description' => [
                        "We have more than 300 services",
                        "Verified experts all arround the country"
                    ],
                    'images' => [
                        'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/74/600.jpg',
                        'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/74/600.jpg',
                        'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/74/600.jpg',
                        'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/74/600.jpg'
                    ]
                ];
            }
            $info['questions'] = $options;
        }
        array_forget($info, 'variables');
        return api_response($request, $info, 200, ['service' => $info]);
    }

    /**
     * @param PriceCalculation $price_calculation
     * @param $prices
     * @param UpsellCalculation $upsell_calculation
     * @param LocationService $location_service
     * @return Collection
     */
    private function formatOptionWithPrice(PriceCalculation $price_calculation, $prices,
                                           UpsellCalculation $upsell_calculation, LocationService $location_service)
    {
        $options = collect();
        foreach ($prices as $key => $price) {
            $option_array = explode(',', $key);
            $options->push([
                'option' => collect($option_array)->map(function ($key) {
                    return (int)$key;
                }),
                'price' => $price_calculation->setOption($option_array)->getUnitPrice(),
                'upsell_price' => $upsell_calculation->setOption($option_array)->getAllUpsellWithMinMaxQuantity()
            ]);
        }
        return $options;
    }

}