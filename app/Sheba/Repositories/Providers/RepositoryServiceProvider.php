<?php namespace Sheba\Repositories\Providers;


use Illuminate\Support\ServiceProvider;
use Sheba\Repositories\Business\FormTemplateItemRepository;
use Sheba\Repositories\Business\FormTemplateRepository;
use Sheba\Repositories\Interfaces\FormTemplateItemRepositoryInterface;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;


class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FormTemplateRepositoryInterface::class, FormTemplateRepository::class);
        $this->app->bind(FormTemplateItemRepositoryInterface::class, FormTemplateItemRepository::class);
    }
}