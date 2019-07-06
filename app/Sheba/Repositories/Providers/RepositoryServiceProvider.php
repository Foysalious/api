<?php namespace Sheba\Repositories\Providers;

use Illuminate\Support\ServiceProvider;
use Sheba\Repositories\Business\DriverRepository;
use Sheba\Repositories\Business\FormTemplateItemRepository;
use Sheba\Repositories\Business\FormTemplateQuestionRepository;
use Sheba\Repositories\Business\FormTemplateRepository;
use Sheba\Repositories\Business\FuelLogRepository;
use Sheba\Repositories\Business\InspectionItemRepository;
use Sheba\Repositories\Business\InspectionItemStatusLogRepository;
use Sheba\Repositories\Business\InspectionRepository;
use Sheba\Repositories\Business\InspectionScheduleRepository;
use Sheba\Repositories\Business\IssueRepository;
use Sheba\Repositories\Business\ProcurementItemFieldRepository;
use Sheba\Repositories\Business\ProcurementItemRepository;
use Sheba\Repositories\Business\ProcurementQuestionRepository;
use Sheba\Repositories\Business\ProcurementRepository;
use Sheba\Repositories\Business\PurchaseRequestItemFieldRepository;
use Sheba\Repositories\Business\PurchaseRequestItemRepository;
use Sheba\Repositories\Business\PurchaseRequestQuestionRepository;
use Sheba\Repositories\Business\PurchaseRequestRepository;
use Sheba\Repositories\Interfaces\DriverRepositoryInterface;
use Sheba\Repositories\Interfaces\FormTemplateItemRepositoryInterface;
use Sheba\Repositories\Interfaces\FormTemplateQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Sheba\Repositories\Interfaces\FuelLogRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionItemStatusLogRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionScheduleRepositoryInterface;
use Sheba\Repositories\Interfaces\IssueRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementItemFieldRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementItemRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Repositories\Interfaces\PurchaseRequestItemFieldRepositoryInterface;
use Sheba\Repositories\Interfaces\PurchaseRequestItemRepositoryInterface;
use Sheba\Repositories\Interfaces\PurchaseRequestQuestionRepositoryInterface;
use Sheba\Repositories\Interfaces\PurchaseRequestRepositoryInterface;

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
        $this->app->bind(FuelLogRepositoryInterface::class, FuelLogRepository::class);
        $this->app->bind(FormTemplateQuestionRepositoryInterface::class, FormTemplateQuestionRepository::class);
        $this->app->bind(ProcurementRepositoryInterface::class, ProcurementRepository::class);
        $this->app->bind(ProcurementItemRepositoryInterface::class, ProcurementItemRepository::class);
        $this->app->bind(ProcurementQuestionRepositoryInterface::class, ProcurementQuestionRepository::class);
        $this->app->bind(PurchaseRequestRepositoryInterface::class, PurchaseRequestRepository::class);
        $this->app->bind(PurchaseRequestItemRepositoryInterface::class, PurchaseRequestItemRepository::class);
        $this->app->bind(PurchaseRequestItemFieldRepositoryInterface::class, PurchaseRequestItemFieldRepository::class);
        $this->app->bind(PurchaseRequestQuestionRepositoryInterface::class, PurchaseRequestQuestionRepository::class);
        $this->app->bind(ProcurementItemFieldRepositoryInterface::class, ProcurementItemFieldRepository::class);
        $this->app->bind(DriverRepositoryInterface::class, DriverRepository::class);
    }
}