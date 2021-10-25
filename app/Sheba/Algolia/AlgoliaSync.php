<?php namespace Sheba\Algolia;

use App\Console\Commands\Command;
use Sheba\Dal\Category\Category;
use Sheba\Dal\Service\Service;

class AlgoliaSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheba:sync-algolia-services';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync category and services sync';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $categories = Category::get();
        /** @var Category $category */
        foreach ($categories as $category) {
            if ((int)$category->publication_status) $category->searchable();
            else $category->unsearchable();
        }
        $services = Service::get();
        /** @var Service $service */
        foreach ($services as $service) {
            $service->searchable();
        }
    }
}
