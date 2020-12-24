<?php namespace Sheba\Algolia\Listeners;


use Sheba\Dal\Category\Category;
use Sheba\Report\Listeners\BaseSavedListener;
use Sheba\Dal\Category\Events\CategorySaved as CategorySavedEvent;

class CategoryUpdateListener extends BaseSavedListener
{

    public function handle(CategorySavedEvent $event)
    {
        /** @var Category $category */
        $category = $event->model;
        $category->pushToIndex();
        foreach ($category->services as $service) {
            $service->pushToIndex();
        }
    }
}