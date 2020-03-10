<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ProductUpload extends Command
{
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
     */
    public function handle()
    {
        $services = $this->getServices()->map(function (Service $service) {
            return $this->addStartPriceToService($service);
        });

        $filename = 'products.csv';
        Storage::disk('s3')->put("uploads/product_feeds/$filename", $this->makeCsv($services, $filename), 'public');
    }

    /**
     * @return Collection
     */
    private function getServices()
    {
        return Service::published()->with(['partners' => function ($q) {
            $q->where([
                ['is_published', 1],
                ['is_verified', 1]
            ])->whereHas('locations', function ($query) {
                $query->where('id', 4);
            });
        }])->get();
    }

    private function addStartPriceToService(Service $service)
    {
        $partners = $service->partners;
        if (count($partners) == 0) return $service;

        $partner_prices = [];
        if ($service->isOptions()) {
            foreach ($partners as $partner) {
                $partner_prices[] = (float)min(json_decode($partner->pivot->prices, true));
            }
        } elseif ($service->isFixed()) {
            foreach ($partners as $partner) {
                $partner_prices[] = (float)$partner->pivot->prices;
            }
        }

        $service->start_price = min($partner_prices);
        unset($service->partners);
        return $service;
    }

    private function makeCsv($services, $filename)
    {
        return Excel::create($filename, function ($excel) use ($services, $filename) {
            $excel->setTitle($filename);
            $excel->setCreator('Sheba')->setCompany('Sheba');
            $excel->sheet('Order', function ($sheet) use ($services) {
                $sheet->loadView('excels.products')->with('services', $services);
            });
        })->string('csv');
    }
}
