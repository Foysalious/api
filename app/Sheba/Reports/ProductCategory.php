<?php

namespace App\Sheba\Reports;

use App\Repositories\DiscountRepository;
use App\Repositories\ServiceRepository;
use Sheba\Dal\Category\Category;
use Sheba\Dal\LocationService\LocationService;
use Excel;
use Exception;
use Illuminate\Support\Facades\Storage;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\StartPriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\Reports\ExcelHandler;
use Sheba\Service\CategoryDeeplinkGenerator;
use Sheba\Service\MinMaxPrice;
use Throwable;
use function MongoDB\is_string_array;

class ProductCategory
{
    private $services;
    private $viewFileName;
    private $uploadFileName;
    private $withoutImageUploadFileName;
    private $priceCalculation;
    private $upsellCalculation;
    private $discountCalculation;
    private $excel;

    public function __construct(ExcelHandler $excel, PriceCalculation $priceCalculation, UpsellCalculation $upsellCalculation, DiscountCalculation $discountCalculation)
    {
        $this->priceCalculation = $priceCalculation;
        $this->upsellCalculation = $upsellCalculation;
        $this->discountCalculation = $discountCalculation;
        $this->services = [];
        $this->viewFileName = "product_categories";
        $this->uploadFileName = 'product_categories.csv';
        $this->withoutImageUploadFileName = 'product_categories_without_image.csv';
        $this->excel = $excel;
    }

    public function calculate()
    {
        $categories = Category::published()->whereHas('services', function ($q) {
            $q->published()->whereHas('locationServices', function ($q) {
                $q->where('location_service.location_id', 4);
            });
        })->whereHas('locations', function ($q) {
            $q->where('locations.id', 4);
        })->with(['services' => function ($q) {
            $q->select('id', 'name', 'category_id', 'min_quantity', 'variable_type', 'is_add_on')->published()->with(['locationServices' => function ($q) {
                $q->where('location_service.location_id', 4);
            }]);
        }, 'parent' => function ($q) {
            $q->select('id', 'name');
        }])->where('parent_id', '<>', null)->select('id', 'name', 'short_description', 'publication_status', 'slug', 'thumb', 'parent_id', 'app_thumb', 'catalog_thumb', 'google_product_category','facebook_product_category','catalog_price')->get();

        foreach ($categories as &$category) {
            $service = $this->getLowestPriceService($category);

            $start_price = app()->make(StartPriceCalculation::class)->getStartPrice($service);
            if ($start_price === false) continue;

            $deeplink = (new CategoryDeeplinkGenerator())->getDeeplink($category);
            $deeplink_without_zxing = (new CategoryDeeplinkGenerator())->getDeeplink($category, false);
            array_add($category, 'android_url', $deeplink['android']);
            array_add($category, 'ios_url', $deeplink['ios']);
            array_add($category, 'android_url_without_zxing', $deeplink_without_zxing['android']);
            array_add($category, 'ios_url_without_zxing', $deeplink_without_zxing['ios']);
            array_add($category, 'sub_link', $deeplink_without_zxing["sub_link"]);
            array_add($category, 'ios_app_name', config('sheba.ios_app_name'));
            array_add($category, 'ios_app_store_id', config('sheba.ios_app_store_id'));
            array_add($category, 'android_app_name', config('sheba.android_app_name'));
            array_add($category, 'android_app_store_id', config('sheba.android_app_store_id'));
            array_add($category, 'web_link',  $category->getSlug());
            array_add($category, 'web_deeplink', 'sub-category/'.$category->id);
            array_add($category, 'start_price', $start_price);
        }
        $this->services = $categories;
        return $this;
    }

    private function getOptionOfMinPriceCombination($prices)
    {
        try {
            return array_keys($prices, min($prices))[0];
        } catch (Throwable $e) {
            return null;
        }
    }

    public function get()
    {
        return $this->services;
    }

    /**
     * @throws Exception
     */
    public function upload()
    {
        Storage::disk('s3')->put("uploads/product_feeds/$this->uploadFileName", $this->makeReport(), 'public');
        Storage::disk('s3')->put("uploads/product_feeds/$this->withoutImageUploadFileName", $this->makeReportWithoutImage(), 'public');
    }

    public function view()
    {
        return view($this->viewFileName)->with('services', $this->services);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function makeReport()
    {
        $this->excel->setFilename($this->uploadFileName);
        $this->excel->setSheetName("Product");
        $this->excel->setViewFile($this->viewFileName);
        $this->excel->pushData('services', $this->services);
        return $this->excel->get();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function makeReportWithoutImage()
    {
        $this->excel->setFilename($this->withoutImageUploadFileName);
        $this->excel->setSheetName("Product");
        $this->excel->setViewFile("product_categories_without_image");
        $this->excel->pushData('services', $this->services);
        return $this->excel->get();
    }

    private function getLowestPriceService(Category $category)
    {
        $minPriceOfService = null;
        $selectedService = $category->services->first();
        foreach ($category->services as $key => $single_service) {
            if ($single_service->is_add_on == 1) continue;

            $service_min_price = app()->make(StartPriceCalculation::class)->getStartPrice($single_service);
            if ($service_min_price === false) continue;

            if ($minPriceOfService === null) {
                $minPriceOfService = $service_min_price;
                $selectedService = $single_service;
            } elseif ($service_min_price < $minPriceOfService) {
                $minPriceOfService = $service_min_price;
                $selectedService = $single_service;
            }
        }
        return $selectedService;
    }
}
