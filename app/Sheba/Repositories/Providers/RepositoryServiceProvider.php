<?php namespace Sheba\Repositories\Providers;


use Illuminate\Support\ServiceProvider;
use Sheba\Repositories\Business\FormTemplateItemRepository;
use Sheba\Repositories\Business\FormTemplateRepository;
use Sheba\Repositories\Business\InspectionItemRepository;
use Sheba\Repositories\Business\InspectionRepository;
use Sheba\Repositories\Interfaces\FormTemplateItemRepositoryInterface;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;


class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FormTemplateRepositoryInterface::class, FormTemplateRepository::class);
        $this->app->bind(FormTemplateItemRepositoryInterface::class, FormTemplateItemRepository::class);
        $this->app->bind(InspectionRepositoryInterface::class, InspectionRepository::class);
        $this->app->bind(InspectionItemRepositoryInterface::class, InspectionItemRepository::class);
    }
}