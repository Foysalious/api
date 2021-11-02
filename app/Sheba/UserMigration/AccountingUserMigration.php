<?php

namespace App\Sheba\UserMigration;

use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Exception;
use Sheba\Dal\UserMigration\UserStatus;

class AccountingUserMigration extends UserMigrationRepository
{
    public function getBanner(): string
    {
        return Constants::$accounting_migration_url . "/accounting_migration_banner.png";
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
            'data'   => $response
        ];
    }

    /**
     * @throws AccountingEntryServerError
     * @throws Exception
     */
    public function updateStatus($status)
    {
//        if ($status == UserStatus::UPGRADING) {
//            $currentStatus = $this->getStatus();
//            /** @var \Sheba\AccountingEntry\Repository\UserMigrationRepository $accUpgradeRepo */
//            $accUpgradeRepo = app(\Sheba\AccountingEntry\Repository\UserMigrationRepository::class);
//            $accUpgradeRepo->migrateInAccounting($this->userId, $currentStatus);
//        }
        return $this->updateMigrationStatus($status);
    }

    private function getPendingResponse(): array
    {
        return [
            "icon" => Constants::$accounting_migration_url . '/accounting_pending.png',
            "header" => "হিসাব খাতা আপগ্রেড করুন।",
            "body" => '<center> হিসাবখাতা ব্যাবহার করতে নতুন সিস্টেম এ আপগ্রেড করা জরুরী। <br />  নতুন হিসাবখাতায় যা যা থাকছে <br /> <b>- লাভ ক্ষতির হিসাব</b><br /><b>- ক্যাশ হিসাব</b><br /><b>- আর অনেক</b></center>',
            "confirm_text" => "আপগ্রেড করুন",
            "cancel_text" => "পুরাতন হিসাব খাতায় থাকতে চাই",
            "dialog_cancelable" => false,
            "migrating_icon" => Constants::$accounting_migration_url . "/accounting_upgrading.png",
            "migrating_text" => "হিসাব খাতা আপগ্রেড হচ্ছে। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন।",
        ];
    }

    private function getUpgradingResponse(): array
    {
        return [
            "migrating_icon" => Constants::$accounting_migration_url . "/accounting_upgrading.png",
            "migrating_text" => "হিসাব খাতা আপগ্রেড হচ্ছে। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন।",
            "dialog_cancelable" => false
        ];
    }

    private function getUpgradedResponse(): array
    {
        return [
            "icon" => Constants::$accounting_migration_url . "/accounting_upgraded.png",
            "header" => "অভিনন্দন",
            "dialog_text" => "হিসাবখাতা সফল ভাবে আপগ্রেড হয়েছে।",
            "button_text" => "হিসাবখাতায় যান",
            "dialog_cancelable" => false
        ];
    }

    private function getFailedResponse(): array
    {
        return [
            "icon" => Constants::$accounting_migration_url . "/accounting_failed.png",
            "header" => "দুঃখিত",
            "dialog_text" => "হিসাবখাতা আপগ্রেড হয়নি।",
            "button_text" => "আবার চেষ্টা করুন",
            "dialog_cancelable" => false,
            "migrating_icon" => Constants::$accounting_migration_url . "/accounting_upgrading.png",
            "migrating_text" => "হিসাব খাতা আপগ্রেড হচ্ছে। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন।",
        ];
    }

}