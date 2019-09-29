<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Service;
use Excel;
use Illuminate\Support\Facades\Storage;

class ProductUpload extends Command
{

    public function __construct()
    {
        parent::__construct();
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
            $calculated_service = Service::with(['partners' => function ($q) {
                $q->where([
                    ['is_published', 1],
                    ['is_verified', 1]
                ])->whereHas('locations', function ($query) {
                    $query->where('id', 4);
                });
            }])->where('id', $service->id)->first();

            $partners = $calculated_service->partners;
            if (count($partners) > 0) {
                if ($service->variable_type == 'Options') {
                    $price = array();
                    foreach ($partners as $partner) {
                        $min = min((array)json_decode($partner->pivot->prices));
                        array_push($price, (float)$min);
                    }
                    array_add($service, 'start_price', min($price));
                } elseif ($service->variable_type == 'Fixed') {
                    $price = array();
                    foreach ($partners as $partner) {
                        array_push($price, (float)$partner->pivot->prices);
                    }
                    array_add($service, 'start_price', min($price));
                }
                array_forget($service, 'partners');
            }
        }
        $filename = 'products.csv';
        $a = Excel::create($filename, function ($excel) use ($services, $filename) {
            $excel->setTitle($filename);
            $excel->setCreator('Sheba')->setCompany('Sheba');
            $excel->sheet('Order', function ($sheet) use ($services) {
                $sheet->loadView('excels.products')->with('services', $services);
            });
        })->string('csv');
        $s3 = Storage::disk('s3');
        $s3->put('uploads/product_feeds/' . $filename, $a, 'public');
    }
}