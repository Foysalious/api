<?php

namespace App\Sheba\UserMigration;

use Sheba\Dal\UserMigration\UserStatus;

class AccountingUserMigration extends UserMigrationRepository
{
    public function getBanner()
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

    public function updateStatus($status)
    {
        //TODO: user migration off for Razoan
//        if ($status == UserStatus::UPGRADING) {
//            /** @var \Sheba\AccountingEntry\Repository\UserMigrationRepository $accUpgradeRepo */
//            $accUpgradeRepo = app(\Sheba\AccountingEntry\Repository\UserMigrationRepository::class);
//            $accUpgradeRepo->migrateInAccounting($this->userId);
//        }
        return $this->updateMigrationStatus($status);
    }

    private function getPendingResponse()
    {
        return [
            "icon" => "https://i.picsum.photos/id/1066/200/300.jpg?hmac=s9I5yyFYG2Di_yr3kg5mcCmdsZkNeQJVsYVhTpnSnnw",
            "header" => "হিসাব খাতা আপগ্রেড করুন।",
            "body" => '<center> হিসাবখাতা ব্যাবহার করতে নতুন সিস্টেম এ আপগ্রেড করা জরুরী। <br />  নতুন হিসাবখাতায় যা যা থাকছে <br /> <b>- লাভ ক্ষতির হিসাব</b><br /><b>- ক্যাশ হিসাব</b><br /><b>- আর অনেক</b></center>',
            "confirm_text" => "আপগ্রেড করুন",
            "cancel_text" => "পুরাতন হিসাব খাতায় থাকতে চাই",
            "dialog_cancelable" => false
        ];
    }

    private function getUpgradingResponse()
    {
        return [
            "icon" => "https://i.picsum.photos/id/1066/200/300.jpg?hmac=s9I5yyFYG2Di_yr3kg5mcCmdsZkNeQJVsYVhTpnSnnw",
            "migrating_text" => "হিসাব খাতা আপগ্রেড হচ্ছে। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন।",
            "dialog_cancelable" => false
        ];
    }

    private function getUpgradedResponse()
    {
        return [
            "icon" => "https://i.picsum.photos/id/1066/200/300.jpg?hmac=s9I5yyFYG2Di_yr3kg5mcCmdsZkNeQJVsYVhTpnSnnw",
            "header" => "অভিনন্দন",
            "dialog_text" => "হিসাবখাতা সফল ভাবে আপগ্রেড হয়েছে।",
            "button_text" => "হিসাবখাতায় যান",
            "dialog_cancelable" => false
        ];
    }

    private function getFailedResponse() {
        return [
            "icon" => "https://i.picsum.photos/id/1066/200/300.jpg?hmac=s9I5yyFYG2Di_yr3kg5mcCmdsZkNeQJVsYVhTpnSnnw",
            "header" => "দুঃখিত",
            "dialog_text" => "হহিসাবখাতা আপগ্রেড হয়নি।",
            "button_text" => "আবার চেষ্টা করুন",
            "dialog_cancelable" => false
        ];
    }

}