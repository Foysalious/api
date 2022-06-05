<?php namespace Sheba\Reports;

use Sheba\LocationService\StartPriceCalculation;
use Sheba\Dal\Service\Service;
use Excel;
use Illuminate\Support\Facades\Storage;
use Sheba\Service\ServiceDeeplinkGenerator;

class Product
{
    private $services;
    private $viewFileName;
    private $uploadFileName;
    private $withoutImageUploadFileName;

    private $excel;

    public function __construct(ExcelHandler $excel)
    {
        $this->services = [];
        $this->viewFileName = "products";
        $this->uploadFileName = 'products.csv';
        $this->withoutImageUploadFileName = "products_without_image.csv";
        $this->excel = $excel;
    }

    public function calculate()
    {
        $services = Service::published()->isNotCrossSaleService()->whereHas('locationServices', function ($q) {
            $q->where('location_service.location_id', 4);
        })->whereHas('category', function ($q) {
            $q->published();
        })->with(['locationServices' => function ($q) {
            $q->where('location_service.location_id', 4);
        }, 'category' => function ($q) {
            $q->select('id', 'parent_id','name')->with(['parent' => function ($q) {
                $q->select('id', 'name');
            }]);
        }])->select('id', 'name', 'short_description', 'publication_status', 'min_quantity', 'variable_type', 'category_id', 'thumb', 'slug', 'app_thumb', 'catalog_thumb', 'google_product_category', 'facebook_product_category', 'catalog_price')->get();
        foreach ($services as $service) {
            $start_price = app()->make(StartPriceCalculation::class)->getStartPrice($service);
            if ($start_price === false) continue;

            $deeplink = (new ServiceDeeplinkGenerator())->getDeeplink($service);
            $deeplink_without_zxing = (new ServiceDeeplinkGenerator())->getDeeplink($service, false);
            array_add($service, 'android_url', $deeplink["android"]);
            array_add($service, 'ios_url', $deeplink["ios"]);
            array_add($service, 'android_url_without_zxing', $deeplink_without_zxing["android"]);
            array_add($service, 'ios_url_without_zxing', $deeplink_without_zxing["ios"]);
            array_add($service, 'sub_link', $deeplink_without_zxing["sub_link"]);
            array_add($service, 'ios_app_name', config('sheba.ios_app_name'));
            array_add($service, 'ios_app_store_id', config('sheba.ios_app_store_id'));
            array_add($service, 'android_app_name', config('sheba.android_app_name'));
            array_add($service, 'android_app_store_id', config('sheba.android_app_store_id'));
            array_add($service, 'web_link', $service->getSlug());
            array_add($service, 'web_deeplink', 'sub-category_service_detail/service-details/'.$service->id.'?category_id='.$service->category->id);
            array_add($service, 'start_price', $start_price);
            removeRelationsAndFields($service);
        }
        $this->services = $services;
        return $this;
    }

    public function get()
    {
        return $this->services;
    }

    /**
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
     */
    private function makeReportWithoutImage()
    {
        $this->excel->setFilename($this->withoutImageUploadFileName);
        $this->excel->setSheetName("Product");
        $this->excel->setViewFile("products_without_image");
        $this->excel->pushData('services', $this->services);
        return $this->excel->get();
    }
}