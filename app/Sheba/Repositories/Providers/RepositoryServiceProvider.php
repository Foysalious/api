<?php namespace Sheba\Repositories\Providers;

use Illuminate\Support\ServiceProvider;
use Sheba\Repositories\Business\FormTemplateItemRepository;
use Sheba\Repositories\Business\FormTemplateRepository;
use Sheba\Repositories\Business\InspectionItemRepository;
use Sheba\Repositories\Business\InspectionItemStatusLogRepository;
use Sheba\Repositories\Business\InspectionRepository;
use Sheba\Repositories\Business\InspectionScheduleRepository;
use Sheba\Repositories\Business\IssueRepository;
use Sheba\Repositories\Interfaces\FormTemplateItemRepositoryInterface;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemStatusLogRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionScheduleRepositoryInterface;
use Sheba\Repositories\Interfaces\IssueRepositoryInterface;


class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FormTemplateRepositoryInterface::class, FormTemplateRepository::class);
        $this->app->bind(FormTemplateItemRepositoryInterface::class, FormTemplateItemRepository::class);
        $this->app->bind(InspectionRepositoryInterface::class, InspectionRepository::class);
        $this->app->bind(InspectionItemRepositoryInterface::class, InspectionItemRepository::class);
        $this->app->bind(InspectionItemStatusLogRepositoryInterface::class, InspectionItemStatusLogRepository::class);
        $this->app->bind(IssueRepositoryInterface::class, IssueRepository::class);
        $this->app->bind(InspectionScheduleRepositoryInterface::class, InspectionScheduleRepository::class);
    }
}