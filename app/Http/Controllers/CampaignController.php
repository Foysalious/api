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
                dd(explode('\\', $offer->target_type));
                $offer = [
                    "target_type"=> "secondary_category",
                    "target_id"=> 19,
                    "name"=> "Gas Stove/Burner Repair",
                    "icon"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/categories/19/icon_png.png",
                    "icon_png"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/categories/19/icon.svg",
                    "app_thumb"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Sub-catagory/19/150.jpg",
                    "thumb"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Sub-catagory/19/600.jpg",
                    "app_banner"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1531900553_gas_stove/burner_repair.jpg",
                    "banner"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1531900553_gas_stove/burner_repair.jpg",
                    "video"=> null,
                    "is_parent"=> false,
                    "is_flash"=> null,
                    "is_video"=> null,
                    "height"=> null,
                    "valid_till"=> null,
                    "voucher_code"=> null,
                    "updated_at"=> "2019-02-05 12=>40=>34",
                    "updated_at_timestamp"=> 1549348834,
                    "ratio"=> null,
                    "package_name"=> null,
                    "link"=> null,
                    "children"=> null,
                    "slug"=> "gas-stoveburner-repair",
                    "start_date"=> null,
                    "end_date"=> null,
                    "variables"=> null
                ];
            }*/
            $offer_one = [
                "target_type"=> "secondary_category",
                "target_id"=> 19,
                "title"=> "Gas Stove/Burner Repair",
                "description"=> "Nunc nulla. Maecenas nec odio et ante tincidunt tempus. Praesent porttitor, nulla vitae posuere iaculis, arcu nisl dignissim dolor, a pretium mi sem ut ipsum. Praesent ac sem eget est egestas volutpat.Quisque rutrum. Praesent nonummy mi in odio. Vivamus consectetuer hendrerit lacus. Fusce pharetra convallis urna.Nulla porta dolor. Duis lobortis massa imperdiet quam. Vivamus euismod mauris. Vestibulum turpis sem, aliquet eget, lobortis pellentesque, rutrum eu, nisl.",
                "app_banner"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1531900553_gas_stove/burner_repair.jpg",
                "height"=> null,
                "ratio"=> null,
            ];

            $offer_two = [
                "target_type"=> "voucher",
                "target_id"=> 228193,
                "title"=> "Gas Stove/Burner Repair",
                "description"=> "Nunc nulla. Maecenas nec odio et ante tincidunt tempus. Praesent porttitor, nulla vitae posuere iaculis, arcu nisl dignissim dolor, a pretium mi sem ut ipsum. Praesent ac sem eget est egestas volutpat.Quisque rutrum. Praesent nonummy mi in odio. Vivamus consectetuer hendrerit lacus. Fusce pharetra convallis urna.Nulla porta dolor. Duis lobortis massa imperdiet quam. Vivamus euismod mauris. Vestibulum turpis sem, aliquet eget, lobortis pellentesque, rutrum eu, nisl.",
                "app_banner"=> "https=>//s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/1531900553_gas_stove/burner_repair.jpg",
                "height"=> null,
                "ratio"=> null,
            ];

            array_push($campaigns, $offer_one,$offer_two);
            return api_response($request, $campaigns, 200, ['campaigns' => $campaigns]);
        }catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }


    }
}