<?php namespace App\Sheba\UserMigration;

use App\Exceptions\Pos\DataMigrationException;
use App\Models\Partner;
use Sheba\Dal\UserMigration\UserStatus;
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
            $accounting_status = $this->setModuleName(Modules::EXPENSE)->getStatus();
            if ($accounting_status != UserStatus::UPGRADED) throw new Exception('Please Complete Accounting Migration First!');
            $current_status = $this->setModuleName(Modules::POS)->getStatus();
            if ($current_status == self::NOT_ELIGIBLE) throw new Exception('Sorry! Not Found');
            if ($current_status == UserStatus::UPGRADED) throw new Exception('Sorry! Already Migrated.');
            if ($current_status == UserStatus::UPGRADING ) throw new Exception('Sorry! Already Migrating.');
            $response = $this->updateMigrationStatus($status);
            /** @var DataMigration $dataMigration */
            $dataMigration = app(DataMigration::class);
            $partner = Partner::find($this->userId);
            $dataMigration->setPartner($partner)->migrate();
            return $response;
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

}