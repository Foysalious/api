<?php namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Sheba\Dal\Service\Service;

class UploadSwaggerJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger-upload-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload swagger json at s3';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = 'swagger.json';
        Storage::disk('s3')->put("uploads/apidocs/$filename", file_get_contents('storage/api-docs/api-docs.json'),'public');
    }
}