<?php


namespace Sheba\Loan;

use App\Models\BankUser;
use App\Sheba\Loan\DLSV2\Notification\SMS\SMSHandler;
use Exception;
use Sheba\Dal\PartnerBankLoan\Statuses;
use Sheba\Notification\NotificationHandler;
use Sheba\PushNotificationHandler;

class Notifications
{
    private $title;
    private $message;

    public static function sendLoanNotification($title, $eventType, $eventId)
    {
        notify()->departments([
            9,
            13
        ])->send([
            "title"      => $title,
            'link'       => env('SHEBA_BACKEND_URL') . "/sp-loan/$eventId",
            "type"       => notificationType('Info'),
            "event_type" => $eventType,
            "event_id"   => $eventId
        ]);
    }

    public static function sendStatusChangeNotification($old_status, $new_status, $partner_bank_loan)
    {
        $class             = class_basename($partner_bank_loan);
        $topic             = config('sheba.push_notification_topic_name.manager') . $partner_bank_loan->partner_id;
        $channel           = config('sheba.push_notification_channel_name.manager');
        $sound             = config('sheba.push_notification_sound.manager');
        (new self())->getTitleMessage($new_status, $partner_bank_loan);
        $notification_data = [
            "title"      => 'Loan status changed',
            "message"    => "Loan status has been updated from $old_status to $new_status",
            "sound"      => "notification_sound",
            "event_type" => "App\\Models\\$class",
            "event_id"   => $partner_bank_loan->id
        ];

        (new PushNotificationHandler())->send($notification_data, $topic, $channel, $sound);
    }

    /**
     * @param $partner_bank_loan
     * @param $new_status
     * @param $reason
     */
    public static function sendStatusChangeSms($partner_bank_loan, $new_status, $reason)
    {
        $message = null;
        $type = null;
        if($new_status == Statuses::APPROVED)
        {
            $message = 'প্রিয় '. $partner_bank_loan->partner->getContactPerson() .', আপনার রবি রিচার্জ লোন আবেদন অনুমোদন করা হয়েছে। লোন ক্রেডিট গ্রহণের জন্য অপেক্ষা করুন। প্রয়োজনে কল করুন ১৬৫১৬-এ।';
            $type = 'Loan Approved';
        }
        if($new_status == Statuses::DISBURSED)
        {
            $message = 'প্রিয় '. $partner_bank_loan->partner->getContactPerson(). ', আপনার রবি ক্রেডিট অ্যাকাউন্ট-এ '. convertNumbersToBangla($partner_bank_loan->loan_amount,true, 0) . ' টাকা জমা করা হয়েছে। ব্যালেন্স জানতে আপনার sManager অ্যাপ এর লোন সেকশন চেক করুন। প্রয়োজনে কল করুন ১৬৫১৬-এ।';
            $type = 'Loan Disbursed';
        }
        if($new_status == Statuses::DECLINED)
        {
            $message = 'প্রিয় '.$partner_bank_loan->partner->getContactPerson().', আপনার রবি রিচার্জ লোন আবেদনটি '. $reason .' কারণে মনোনীত হয়নি। প্রয়োজনে কল করুন ১৬৫১৬-এ।';
            $type = 'Loan Declined';
        }
        (new SMSHandler())->setMsg($message)->setMobile($partner_bank_loan->partner->getContactNumber())->setMsgType($type)->setLoanId($partner_bank_loan->id)->shoot();
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

    private function getTitleMessage($status, $partner_bank_loan)
    {
        $partner_name = $partner_bank_loan->partner->name;

        if($status == Statuses::APPROVED){
            $this->title     = "অভিনন্দন! আপনার রবি রিচার্জ লোন আবেদন অনুমোদিত হয়েছে।";
            $this->message   = "প্রিয় $partner_name, আপনার রবি রিচার্জ লোন আবেদন অনুমোদন করা হয়েছে। লোন ক্রেডিট গ্রহণের জন্য অপেক্ষা করুন। প্রয়োজনে কল করুন ১৬৫১৬-এ।";
        }
        elseif ( $status == Statuses::REJECTED || $status == Statuses::DECLINED){
            $this->title     = "দুঃখিত! আপনার রবি রিচার্জ লোন আবেদনটি মনোনীত হয়নি।";
            $this->message   = "প্প্রিয় $partner_name, আপনার রবি রিচার্জ লোন আবেদনটি  কারণে মনোনীত হয়নি। প্রয়োজনে কল করুন ১৬৫১৬-এ।";
        }
    }
}
