<?php namespace App\Sheba\UserMigration;

use App\Exceptions\Pos\DataMigrationException;
use App\Models\Partner;
use Illuminate\Support\Facades\Redis;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\UserMigrationRepository as AccountingUpgradeRepo;
use Sheba\Dal\UserMigration\UserStatus;
use Sheba\Dal\UserMigration\Contract as UserMigrationRepo;
use Sheba\Partner\DataMigration\DataMigration;
use Exception;

class PosUserMigration extends UserMigrationRepository
{
    public function getBanner(): string
    {
        return config('s3.url') . "pos/migration/icons/pos_migration_banner.png";
    }

    public function getStatusWiseResponse(): array
    {
        $status = $this->getStatus();
        $response = null;
        if ($status == UserStatus::PENDING) {
            $response = $this->getPendingResponse();
        } elseif ($status == UserStatus::UPGRADING) {
            $response = $this->getUpgradingResponse();
        } elseif ($status == UserStatus::UPGRADED) {
            $response = $this->getUpgradedResponse();
        } elseif ($status == UserStatus::FAILED) {
            $response = $this->getFailedResponse();
        }
        return [
            'status' => $status,
            'data' => $response
        ];
    }

    /**
     * @throws DataMigrationException
     * @throws Exception
     */
    public function updateStatus($status)
    {
        if ($status == UserStatus::UPGRADING) {
            return $this->migrate();
        } else {
            return $this->updateMigrationStatus($status);
        }
    }

    private function getPendingResponse(): array
    {
        return [
            "icon" => config('s3.url') . "pos/migration/icons/pos_migration_pending.png",
            "header" => "বেচা-বিক্রি আপগ্রেড করুন।",
            "body" => '<center> বেচা-বিক্রি ব্যাবহার করতে নতুন সিস্টেম এ আপগ্রেড করা জরুরী।<br />  নতুন বেচা-বিক্রিতে যা যা থাকছে <br /> <b>- লাভ ক্ষতির হিসাব</b><br /><b>- অর্ডার ম্যানেজমেন্ট</b><br /><b>- আরও অনেক কিছু</b></center>',
            "confirm_text" => "আপগ্রেড করুন",
            "cancel_text" => "পুরাতন বেচা-বিক্রিতে থাকতে চাই",
            "dialog_cancelable" => false,
            "migrating_icon" => config('s3.url') . "pos/migration/icons/pos_migration_upgrading.png",
            "migrating_text" => "বেচা-বিক্রি আপগ্রেড হচ্ছে। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন।",
        ];
    }

    private function getUpgradingResponse(): array
    {
        return [
            "migrating_icon" => config('s3.url') . "pos/migration/icons/pos_migration_upgrading.png",
            "migrating_text" => "বেচা-বিক্রি আপগ্রেড হচ্ছে। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন।",
            "dialog_cancelable" => false
        ];
    }

    private function getUpgradedResponse(): array
    {
        return [
            "icon" => config('s3.url') . "pos/migration/icons/pos_migration_upgraded.png",
            "header" => "অভিনন্দন",
            "dialog_text" => "বেচা-বিক্রি সফলভাবে আপগ্রেড হয়েছে।",
            "button_text" => "বেচা-বিক্রিতে যান",
            "dialog_cancelable" => false
        ];
    }

    private function getFailedResponse(): array
    {
        return [
            "icon" => config('s3.url') . "pos/migration/icons/pos_migration_failed.png",
            "header" => "দুঃখিত",
            "dialog_text" => "বেচা-বিক্রি আপগ্রেড হয়নি।",
            "button_text" => "আবার চেষ্টা করুন",
            "dialog_cancelable" => false,
            "migrating_icon" => config('s3.url') . "pos/migration/icons/pos_migration_upgrading.png",
            "migrating_text" => "বেচা-বিক্রি আপগ্রেড হচ্ছে। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন।",
        ];
    }

    public function versionCodeCheck($appVersion, $modulePayload)
    {
        if ((int)$appVersion >= $modulePayload['app_version']) {
            return [
                "access" => true,
                'message' => 'You are allowed to use.'
            ];

        }
        return [
            "access" => false,
            "icon" => config('s3.url') . "pos/migration/icons/pos_migration_pending.png",
            "header" => "বেচা-বিক্রি আপগ্রেড করেছেন।",
            "message" => "<center>বেচা-বিক্রি ব্যবহার করতে নতুন সিস্টেম <br /> প্লে-স্টোর থেকে আপগ্রেড করা আবশ্যক।<br /> নতুন বেচা-বিক্রিতে যা যা থাকছে <br /> <b>- লাভ ক্ষতির হিসাব</b><br /><b>- অর্ডার ম্যানেজমেন্ট</b><br /><b>- আরও অনেক কিছু</b></center>"
        ];

    }

    private function checkPosMigrationStatus()
    {
        $posMigrationStatus = $this->setModuleName(Modules::POS)->getStatus();
        if ($posMigrationStatus == self::NOT_ELIGIBLE) throw new Exception('Sorry! Not Found');
        if ($posMigrationStatus == UserStatus::UPGRADED) throw new Exception('Sorry! Already Migrated.');
        if ($posMigrationStatus == UserStatus::UPGRADING ) throw new Exception('Sorry! Already Migrating.');
    }

    /**
     * @throws Exception
     */
    private function migrate()
    {
        $this->checkPosMigrationStatus();
        $accMigrationStatus = $this->getMigrationStatus(Modules::EXPENSE);
        if ($accMigrationStatus == self::NOT_ELIGIBLE) {
            /**
             * Accounting migration status updated to 'Pending'
             * Accounting migration process started
             * Pos migration status updated to 'Upgrading'
             * Pos migration process will start after accounting migration completed
             **/
            $this->updateModuleMigrationStatus(UserStatus::PENDING, Modules::EXPENSE);
            $this->migrateToAccounting(UserStatus::UPGRADING);
            return $this->updateMigrationStatus(UserStatus::UPGRADING);
        } else if ($accMigrationStatus == UserStatus::PENDING) {
            /**
             * Accounting migration process started
             * Pos migration status updated to 'Upgrading'
             * Pos migration process will start after accounting migration completed
             **/
            $this->migrateToAccounting(UserStatus::UPGRADING);
            return $this->updateMigrationStatus(UserStatus::UPGRADING);
        } elseif ($accMigrationStatus == UserStatus::UPGRADED) {
            /**
             * Pos migration status updated to 'Upgrading'
             * Pos migration process started
             */
            return $this->migrateToPos();
        } else {
            throw new Exception('Please Complete Accounting Migration First!');
        }
    }

    /**
     * @throws Exception
     */
    private function migrateToPos()
    {
        $response = $this->updateMigrationStatus(UserStatus::UPGRADING);
        /** @var DataMigration $posDataMigration */
        $posDataMigration = app(DataMigration::class);
        $partner = Partner::find($this->userId);
        $posDataMigration->setPartner($partner)->migrate();
        return $response;
    }

    /**
     * @throws AccountingEntryServerError
     * @throws Exception
     */
    private function migrateToAccounting($status)
    {
        if ($status == UserStatus::UPGRADING) {
            $currentStatus = $this->getMigrationStatus(Modules::EXPENSE);
            /** @var AccountingUpgradeRepo $accUpgradeRepo */
            $accUpgradeRepo = app(AccountingUpgradeRepo::class);
            $accUpgradeRepo->migrateInAccounting($this->userId, $currentStatus);
        }
        return $this->updateModuleMigrationStatus($status, Modules::EXPENSE);
    }

    private function getMigrationStatus($moduleName)
    {
        /** @var UserMigrationRepo $userMigrationRepo */
        $userMigrationRepo = app(UserMigrationRepo::class);
        $info = $userMigrationRepo->builder()->where('user_id', $this->userId)->where('module_name', $moduleName)->first();
        if ($info) {
            return $info->status;
        }
        return self::NOT_ELIGIBLE;
    }

    private function updateModuleMigrationStatus($status, $moduleName)
    {
        /** @var UserMigrationRepo $userMigrationRepo */
        $userMigrationRepo = app(UserMigrationRepo::class);
        $info = $userMigrationRepo->builder()->where('user_id', $this->userId)->where('module_name', $moduleName)->first();
        if (!$info) {
            $data = ['status' => $status];
            return $info->update($this->withUpdateModificationField($data));
        }
        if ($info->status == UserStatus::UPGRADED) {
            throw new Exception('Sorry! Already Migrated.');
        }
        if ($info->status == UserStatus::UPGRADING && ($status == UserStatus::UPGRADING || $status == UserStatus::PENDING)) {
            throw new Exception('Sorry! Already Migrating.');
        }
        if ($status == UserStatus::UPGRADING) {
            Redis::set("user-migration:$this->userId", "$this->moduleName");
        }
        // Api call will be halt if migration failed.
        if ($status == UserStatus::UPGRADED) {
            Redis::del("user-migration:$this->userId");
        }
        $data = ['status' => $status];

        return $info->update($this->withUpdateModificationField($data));
    }

}