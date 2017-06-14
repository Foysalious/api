<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Service;
use App\Repositories\ServiceRepository;
use Excel;
use Illuminate\Support\Facades\Storage;

class ProductUpload extends Command
{
    private $serviceRepository;

    public function __construct()
    {
        parent::__construct();
        $this->serviceRepository = new ServiceRepository();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product-upload-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload products & save it to csv at s3';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $services = Service::where('publication_status', 1)->get();
        foreach ($services as $service) {
            $this->serviceRepository->getStartPrice($service);
        }
        $filename = 'yes';
        $a = Excel::create($filename, function ($excel) use ($services, $filename) {
            $excel->setTitle($filename);
            $excel->setCreator('Sheba')->setCompany('Sheba');
            $excel->sheet('Order', function ($sheet) use ($services) {
                $sheet->loadView('excels.products')->with('services', $services);
            });
        })->string('csv');
        $filename = 'products.csv';
        $s3 = Storage::disk('s3');
        $s3->put('uploads/product_feeds/' . $filename, $a, 'public');
    }
}