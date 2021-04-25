<?php namespace Sheba\Algolia\Listeners;


use Sheba\Dal\Category\Category;
use Sheba\Dal\Extras\Events\BaseSavedEvent;
use Sheba\Dal\Service\Service;
use Sheba\Report\Listeners\BaseSavedListener;

class CategoryUpdateListener extends BaseSavedListener
{
    public function handle(BaseSavedEvent $event)
    {
        /** @var Category $category */
        $category = $event->model;
        $category->searchable();
        foreach ($category->services as $service) {
            /** @var $service Service */
            $service->searchable();
        }
    }
}
