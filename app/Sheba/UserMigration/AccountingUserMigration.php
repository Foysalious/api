<?php

namespace App\Sheba\UserMigration;

use Exception;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\UserMigration\UserStatus;

class AccountingUserMigration extends UserMigrationRepository
{
    public function getBanner(): string
    {
        return 'accounting-banner';
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
        if ($status == UserStatus::UPGRADING) {
            /** @var \Sheba\AccountingEntry\Repository\UserMigrationRepository $accUpgradeRepo */
            $accUpgradeRepo = app(\Sheba\AccountingEntry\Repository\UserMigrationRepository::class);
            $accUpgradeRepo->migrateInAccounting($this->userId, $status);
        }
        return $this->updateMigrationStatus($status);
    }

    private function getPendingResponse(): array
    {
        return [
            "icon" => "image.png",
            "header" => "হিসাব খাতা আপগ্রেড করুন।",
            "body" => '<center> হিসাবখাতা ব্যাবহার করতে নতুন সিস্টেম এ আপগ্রেড করা জরুরী। <br />  নতুন হিসাবখাতায় যা যা থাকছে <br /> <b>- লাভ ক্ষতির হিসাব</b><br /><b>- ক্যাশ হিসাব</b><br /><b>- আর অনেক</b></center>',
            "confirm_text" => "আপগ্রেড করুন",
            "cancel_text" => "পুরাতন হিসাব খাতায় থাকতে চাই",
            "dialog_cancelable" => false
        ];
    }

    private function getUpgradingResponse(): array
    {
        return [
            "icon" => "image.png",
            "migrating_text" => "হিসাব খাতা আপগ্রেড হচ্ছে। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন।",
            "dialog_cancelable" => false
        ];
    }

    private function getUpgradedResponse(): array
    {
        return [
            "icon" => "image.png",
            "header" => "অভিনন্দন",
            "dialog_text" => "হিসাবখাতা সফল ভাবে আপগ্রেড হয়েছে।",
            "button_text" => "হিসাবখাতায় যান",
            "dialog_cancelable" => false
        ];
    }

    private function getFailedResponse(): array
    {
        return [
        ];
    }

}