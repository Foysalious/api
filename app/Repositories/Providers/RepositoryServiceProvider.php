<?php namespace App\Repositories\Providers;

use App\Repositories\Business\FormTemplateRepository;
use App\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Illuminate\Support\ServiceProvider;


class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FormTemplateRepositoryInterface::class, FormTemplateRepository::class);
    }
}