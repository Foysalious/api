<?php namespace Sheba\Partner\DataMigration\Jobs;


use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Sheba\Repositories\PartnerRepository;

class PartnerMigrationStartJob
{
    use InteractsWithQueue, SerializesModels;

    private $partner;

    public function __construct($partner)
    {
        $this->partner = $partner;
    }

    public function handle()
    {
        Log::info('Starting Migration of Partner: #'.$this->partner->id. ' at '.Carbon::now());
    }
}