<?php

namespace App\Sheba\Reports;

use App\Models\Category;
use Excel;
use Illuminate\Support\Facades\Storage;
use Sheba\Reports\ExcelHandler;
use Sheba\Repositories\DiscountRepository;
use Sheba\Repositories\ServiceRepository;

class ProductCategory
{
    private $serviceRepo;
    private $discountRepo;
    private $services;
    private $viewFileName;
    private $uploadFileName;
    private $withoutImageUploadFileName;

    private $excel;

    public function __construct(ExcelHandler $excel)
    {
        $this->serviceRepo = new ServiceRepository();
        $this->discountRepo = new DiscountRepository();
        $this->services = [];
        $this->viewFileName = "product_categories";
        $this->uploadFileName = 'product_categories.csv';
        $this->withoutImageUploadFileName = 'product_categories_without_image.csv';
        $this->excel = $excel;
    }

    public function calculate()
    {
        $categories = Category::published()->with(['services' => function ($q) {
            $q->published()->with(['partners' => function ($q) {
                $q->where([
                    ['is_published', 1],
                    ['is_verified', 1],
                    ['partners.status', 'Verified']
                ])->whereHas('locations', function ($query) {
                    $query->where('id', 4);
                });
            }]);
        }])->where('parent_id', '<>', null)->get();
        $categories = $categories->filter(function ($category) {
            return $category->services->count() > 0;
        });
        foreach ($categories as &$category) {
            $service = $category->services->first();
            if ($service == null) {

            }
            $partners = $service->partners;
            if (count($partners) > 0) {
                $start_price = null;
                if ($service->variable_type == 'Options') {
                    $price = [];
                    foreach ($partners as $partner) {
                        $min = min((array)json_decode($partner->pivot->prices));
                        $partner['prices'] = $min;
                        $calculate_partner = $this->discountRepo->addDiscountToPartnerForService($partner);
                        array_push($price, $calculate_partner['discounted_price']);
                    }
                    $start_price = min($price);
                } elseif ($service->variable_type == 'Fixed') {
                    $price = [];
                    foreach ($partners as $partner) {
                        $partner['prices'] = (float)$partner->pivot->prices;
                        $calculate_partner = $this->discountRepo->addDiscountToPartnerForService($partner);
                        array_push($price, $calculate_partner['discounted_price']);
                    }
                    $start_price = min($price);
                }
                array_add($category, 'start_price', $start_price);
            }
        }
        $this->services = $categories;
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
        $this->excel->setViewFile("product_categories_without_image");
        $this->excel->pushData('services', $this->services);
        return $this->excel->get();
    }
}