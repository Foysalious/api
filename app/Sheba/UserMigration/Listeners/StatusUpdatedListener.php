<?php namespace App\Sheba\UserMigration\Listeners;


use App\Models\Partner;
use App\Sheba\UserMigration\Events\StatusUpdated;
use App\Sheba\UserMigration\Modules;
use App\Sheba\UserMigration\UserMigrationRepository;
use App\Sheba\UserMigration\UserMigrationService;
use Exception;
use Sheba\Dal\UserMigration\Contract as UserMigrationRepo;
use Sheba\Dal\UserMigration\UserStatus;
use Sheba\Partner\DataMigration\DataMigration;

class StatusUpdatedListener
{
    /**
     * @throws Exception
     */
    public function handle(StatusUpdated $event)
    {
        if ($event->getModuleName() == Modules::EXPENSE) {
            if ($event->getStatus() == UserStatus::UPGRADED) {
                /** @var UserMigrationRepo $userMigrationRepo */
                $userMigrationRepo = app(UserMigrationRepo::class);
                $userPosMigration = $userMigrationRepo->builder()->where('user_id', $event->getUserId())
                    ->where('module_name', Modules::POS)->first();
                if ($userPosMigration && $userPosMigration->status == UserStatus::UPGRADING) {
                    /** @var DataMigration $posDataMigration */
                    $posDataMigration = app(DataMigration::class);
                    $partner = Partner::find($event->getUserId());
                    $posDataMigration->setPartner($partner)->migrate();
                }
            } elseif ($event->getStatus() == UserStatus::FAILED) {
                /** @var UserMigrationRepo $userMigrationRepo */
                $userMigrationRepo = app(UserMigrationRepo::class);
                $userPosMigration = $userMigrationRepo->builder()->where('user_id', $event->getUserId())
                    ->where('module_name', Modules::POS)->first();
                if ($userPosMigration && $userPosMigration->status == UserStatus::UPGRADING) {
                    /** @var UserMigrationService $userMigrationSvc */
                    $userMigrationSvc = app(UserMigrationService::class);
                    /** @var UserMigrationRepository $class */
                    $class = $userMigrationSvc->resolveClass(Modules::POS);
                    $class->setUserId($event->getUserId())->setModuleName(Modules::POS)->updateStatus(UserStatus::FAILED);
                }
            }
        }
    }

}