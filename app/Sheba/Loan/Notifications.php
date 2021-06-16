<?php


namespace Sheba\Loan;

use App\Models\BankUser;
use App\Sheba\Loan\DLSV2\Notification\SMS\SMSHandler;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Exception;
use Sheba\Dal\PartnerBankLoan\LoanTypes;
use Sheba\Dal\PartnerBankLoan\Statuses;
use Sheba\Notification\NotificationHandler;
use Sheba\PushNotificationHandler;

class Notifications
{

    public static function sendLoanNotification($title, $eventType, $eventId)
    {
        notify()->departments([
            9,
            13
        ])->send([
            "title"      => $title,
            'link'       => config('sheba.admin_url') . "/sp-loan/$eventId",
            "type"       => notificationType('Info'),
            "event_type" => $eventType,
            "event_id"   => $eventId
        ]);
    }

    public static function sendStatusChangeNotification($old_status, $new_status, $partner_bank_loan)
    {
        $class        = class_basename($partner_bank_loan);
        $topic        = config('sheba.push_notification_topic_name.manager') . $partner_bank_loan->partner_id;
        $channel      = config('sheba.push_notification_channel_name.manager');
        $sound        = config('sheba.push_notification_sound.manager');
        $partner_name = $partner_bank_loan->partner->name;
        $log          = $partner_bank_loan->bankLoanLogs()->orderBy('created_at', 'desc')->first();
        $reason       = $log ? $log->description : null;
        $title        = "Loan Status has changed";
        $message      = "Loan status has been changed to $new_status";

        if ($new_status == Statuses::APPROVED) {
            $title   = "অভিনন্দন! আপনার সেবা টপ-আপ ফ্যাসিলিটি রিচার্জ আবেদন অনুমোদিত হয়েছে।";
            $message = "প্রিয় $partner_name, আপনার সেবা টপ-আপ ফ্যাসিলিটি রিচার্জ আবেদন অনুমোদন করা হয়েছে। ক্রেডিট গ্রহণের জন্য অপেক্ষা করুন। প্রয়োজনে কল করুন ১৬৫১৬-এ।";
        } elseif ($new_status == Statuses::DECLINED) {
            $title   = "দুঃখিত! আপনার সেবা টপ-আপ ফ্যাসিলিটি রিচার্জ আবেদনটি মনোনীত হয়নি।";
            $message = "প্প্রিয় $partner_name, আপনার সেবা টপ-আপ ফ্যাসিলিটি রিচার্জ আবেদনটি $reason কারণে মনোনীত হয়নি। প্রয়োজনে কল করুন ১৬৫১৬-এ।";
        } elseif ($new_status == Statuses::DISBURSED) {
            $title   = "আপনার সেবা টপ-আপ ক্রেডিট অ্যাকাউন্ট-এ $partner_bank_loan->loan_amount টাকা জমা হয়েছে।";
            $message = "প্রিয় $partner_name, আপনার সেবা টপ-আপ ফ্যাসিলিটি ক্রেডিট অ্যাকাউন্ট-এ $partner_bank_loan->loan_amount টাকা জমা করা হয়েছে। ব্যালেন্স জানতে আপনার sManager অ্যাপ এর লোন সেকশন চেক করুন। প্রয়োজনে কল করুন ১৬৫১৬-এ।";
        }

        $notification_data = [
            "title"      => $title,
            "message"    => $message,
            "sound"      => "notification_sound",
            "event_type" => "App\\Models\\$class",
            "event_id"   => $partner_bank_loan->id
        ];
        return (new PushNotificationHandler())->send($notification_data, $topic, $channel, $sound);

    }

    /**
     * @param $partner_bank_loan
     * @param $new_status
     * @param $reason
     * @param $user
     */
    public static function sendStatusChangeSms($partner_bank_loan, $new_status, $reason, $user)
    {
        $message = null;
        $type    = null;
        $loan_name = $partner_bank_loan->type === LoanTypes::MICRO ? 'টপ-আপ ফ্যাসিলিটি রিচার্জ' : 'টার্ম লোন';
        if ($new_status == Statuses::APPROVED) {
            $message = 'প্রিয় ' . $partner_bank_loan->partner->getContactPerson() . ', আপনার সেবা '.$loan_name.' আবেদন অনুমোদন করা হয়েছে। ক্রেডিট গ্রহণের জন্য অপেক্ষা করুন। প্রয়োজনে কল করুন ১৬৫১৬-এ।';
            $type    = 'Loan Approved';
        }
        if ($new_status == Statuses::DISBURSED) {
            $message = 'প্রিয় ' . $partner_bank_loan->partner->getContactPerson() . ', আপনার সেবা '.$loan_name.' অ্যাকাউন্ট-এ ' . convertNumbersToBangla($partner_bank_loan->loan_amount, true, 0) . ' টাকা জমা করা হয়েছে। ব্যালেন্স জানতে আপনার sManager অ্যাপ এর লোন সেকশন চেক করুন। প্রয়োজনে কল করুন ১৬৫১৬-এ।';
            $type    = 'Loan Disbursed';
        }
        if ($new_status == Statuses::DECLINED) {
            $message = 'প্রিয় ' . $partner_bank_loan->partner->getContactPerson() . ', আপনার সেবা '.$loan_name.' আবেদনটি ' . $reason . ' কারণে মনোনীত হয়নি। প্রয়োজনে কল করুন ১৬৫১৬-এ।';
            $type    = 'Loan Declined';
        }
        (new SMSHandler())
            ->setMsg($message)
            ->setMobile($partner_bank_loan->partner->getContactNumber())
            ->setMsgType($type)
            ->setLoanId($partner_bank_loan->id)
            ->setUser($user)
            ->setFeatureType(FeatureType::LOAN)
            ->setBusinessType(BusinessType::SMANAGER)
            ->shoot();
    }

    /**
     * @param        $bankId
     * @param        $title
     * @param        $link
     * @param        $eventId
     * @param string $eventType
     * @throws Exception
     */
    public static function toBankUser($bankId, $title, $link, $eventId, $eventType = "App\Models\PartnerBankLoan")
    {
        /** @var NotificationHandler $handler */
        $userIds = BankUser::where('bank_id', $bankId)->pluck('id');
        if (!empty($userIds)) {
            notify()->bankUsers($userIds)->send([
                'title'      => $title,
                'link'       => $link ?: '',
                'type'       => notificationType('Info'),
                'event_type' => $eventType,
                'event_id'   => $eventId
            ]);
        }
    }
}
