<?php namespace App\Sheba\FacebookCatalogue;


use Exception;
use Illuminate\Support\Facades\Storage;
use Sheba\Dal\Category\Category;
use Sheba\Dal\Service\Service;
use Sheba\Reports\ExcelHandler;

class CombinedCatalogue
{
    private $viewFileName;
    private $uploadFileName;
    private $excel;
    /** @var Service[] */
    private $services;
    /** @var Category[] */
    private $categories;

    public function __construct(ExcelHandler $excel)
    {
        $this->viewFileName = "combine_products";
        $this->uploadFileName = 'combine_products.csv';
        $this->excel = $excel;
    }

    /**
     * @param Service[] $services
     * @return CombinedCatalogue
     */
    public function setServices($services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @param Category[] $categories
     * @return CombinedCatalogue
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function upload()
    {
        Storage::disk('s3')->put("uploads/product_feeds/$this->uploadFileName", $this->makeReport(), 'public');
    }

    public function view()
    {
        return view('reports/excels/' . $this->viewFileName)->with('services', $this->services)->with('categories', $this->categories);
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
        $this->excel->pushData('services', $this->services)->pushData('categories', $this->categories);
        return $this->excel->get();
    }

}