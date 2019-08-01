<?php namespace Sheba\Pos\Repositories;


use Illuminate\Support\ServiceProvider;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;

class PosRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PosServiceRepositoryInterface::class, PosServiceRepository::class);
    }
}