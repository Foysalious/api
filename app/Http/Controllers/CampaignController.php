<?php namespace App\Http\Controllers;

use App\Models\OfferShowcase;
use Illuminate\Http\Request;
use App\Models\HyperLocal;
use Throwable;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        try{
            $offers = OfferShowcase::active()->campaign()->valid()->orderBy('created_at', 'DESC')->get();

            /*$location = '';
            if ($request->has('location')) {
                $location = (int)$request->location;
            } elseif ($request->has('lat') && $request->has('lng')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location_id;
            }*/
#end(array_values($yourArray));
            $campaigns = [];
            /*foreach ($offers as $offer){
                $target_type  = explode('\\', $offer->target_type);
                dd(snake_case(end($target_type)));
                $offer = [
                    "target_type"=> "secondary_category",
                    "target_id"=> 19,
                    "title"=> "Gas Stove/Burner Repair",
                    "description"=> "Nunc nulla. Maecenas nec odio et ante tincidunt tempus. Praesent porttitor, nulla vitae posuere iaculis, arcu nisl dignissim dolor, a pretium mi sem ut ipsum. Praesent ac sem eget est egestas volutpat.Quisque rutrum. Praesent nonummy mi in odio. Vivamus consectetuer hendrerit lacus. Fusce pharetra convallis urna.Nulla porta dolor. Duis lobortis massa imperdiet quam. Vivamus euismod mauris. Vestibulum turpis sem, aliquet eget, lobortis pellentesque, rutrum eu, nisl.",
                    "image"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1531900553_gas_stove/burner_repair.jpg",
                ];
            }*/
            $offer_one = [
                "target_type"=> "secondary_category",
                "target_id"=> 19,
                "title"=> "Gas Stove/Burner Repair",
                "description"=> "Nunc nulla. Maecenas nec odio et ante tincidunt tempus. Praesent porttitor, nulla vitae posuere iaculis, arcu nisl dignissim dolor, a pretium mi sem ut ipsum. Praesent ac sem eget est egestas volutpat.Quisque rutrum. Praesent nonummy mi in odio. Vivamus consectetuer hendrerit lacus. Fusce pharetra convallis urna.Nulla porta dolor. Duis lobortis massa imperdiet quam. Vivamus euismod mauris. Vestibulum turpis sem, aliquet eget, lobortis pellentesque, rutrum eu, nisl.",
                "image"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1531900553_gas_stove/burner_repair.jpg",
            ];

            $offer_two = [
                "target_type"=> "voucher",
                "target_id"=> 228193,
                "title"=> "Gas Stove/Burner Repair",
                "description"=> "Nunc nulla. Maecenas nec odio et ante tincidunt tempus. Praesent porttitor, nulla vitae posuere iaculis, arcu nisl dignissim dolor, a pretium mi sem ut ipsum. Praesent ac sem eget est egestas volutpat.Quisque rutrum. Praesent nonummy mi in odio. Vivamus consectetuer hendrerit lacus. Fusce pharetra convallis urna.Nulla porta dolor. Duis lobortis massa imperdiet quam. Vivamus euismod mauris. Vestibulum turpis sem, aliquet eget, lobortis pellentesque, rutrum eu, nisl.",
                "image"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1531900553_gas_stove/burner_repair.jpg",
            ];

            array_push($campaigns, $offer_one,$offer_two);
            return api_response($request, $campaigns, 200, ['campaigns' => $campaigns]);
        }catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }


    }
}