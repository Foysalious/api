<?php namespace Sheba\Reports;

use App\Models\Service;
use Excel;
use Illuminate\Support\Facades\Storage;
use Sheba\Repositories\DiscountRepository;
use Sheba\Repositories\ServiceRepository;

class Product
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
        $this->viewFileName = "products";
        $this->uploadFileName = 'products.csv';
        $this->withoutImageUploadFileName = "products_without_image.csv";
        $this->excel = $excel;
    }

    public function calculate()
    {
        $services = Service::published()->get();
        foreach ($services as $service) {
            $calculated_service = Service::with(['partners' => function ($q) {
                $q->where([
                    ['is_published', 1],
                    ['is_verified', 1],
                    ['partners.status', 'Verified']
                ])->whereHas('locations', function ($query) {
                    $query->where('id', 4);
                });
            }])->where('id', $service->id)->first();

            $partners = $calculated_service->partners;
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
                array_add($service, 'start_price', $start_price);
                array_forget($service, 'partners');
            }
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