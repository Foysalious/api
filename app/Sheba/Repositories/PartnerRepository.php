<?php namespace Sheba\Repositories;

use App\Models\Partner;
use App\Models\PartnerStatusChangeLog;
use Exception;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\Partner\PartnerRepositoryInterface;

class PartnerRepository extends BaseRepository implements PartnerRepositoryInterface
{
    public function __construct(Partner $partner)
    {
        parent::__construct();
        $this->setModel($partner);
    }

    /**
     * @param Partner $partner
     * @param $amount
     * @param $type
     * @throws Exception
     */
    public function updateWallet(Partner $partner, $amount, $type)
    {
        $new_wallet = ($type == 'Debit') ? ($partner->wallet - $amount) : ($partner->wallet + $amount);
        $this->update($partner, ['wallet' => $new_wallet]);

        $min_wallet_threshold = $partner->walletSetting->min_wallet_threshold;

        if ($new_wallet < $min_wallet_threshold) {
            $this->sendWalletExceededNotification($partner);
        } else if ($new_wallet < ($min_wallet_threshold + 500)) {
            $this->sendWalletWarningNotification($partner, $partner->wallet);
        } else if ($new_wallet < ($min_wallet_threshold + 2000)) {
            $this->sendWalletWarningNotification($partner, $partner->wallet);
        }
    }

    public function saveStatusChangeLog(Partner $partner, $data)
    {
        $partner->statusChangeLogs()->save(new PartnerStatusChangeLog($this->withCreateModificationField($data)));
    }

    /**
     * @param Partner $partner
     * @param $amount
     * @throws Exception
     */
    private function sendWalletWarningNotification(Partner $partner, $amount)
    {
        //$notification = "Your wallet is running low. You have left $warning_state of your deposit.";
        $amount = en2bnNumber($amount);
        $notification = "আপনার ওয়ালেট ব্যালেন্স, লিমিট অতিক্রম করতে চলেছে, মাত্র $amount টাকা ব্যালেন্স অবশিষ্ট রয়েছে, অনুগ্রহ করে রিচার্জ করুন";
        $this->sendWalletNotification($partner, $notification);
    }

    /**
     * @param Partner $partner
     * @throws Exception
     */
    private function sendWalletExceededNotification(Partner $partner)
    {
        $notification = "আপনার ওয়ালেট ব্যালেন্স লিমিট অতিক্রম করেছে, অর্ডার পেতে অনুগ্রহ করে রিচার্জ করুন";
        $this->sendWalletNotification($partner, $notification);
    }

    /**
     * @param Partner $partner
     * @param $notification
     * @throws Exception
     */
    private function sendWalletNotification(Partner $partner, $notification)
    {
        notify()->partner($partner)->send([
            "title" => $notification,
            "link" => config('sheba.partners_url') .'/'. $partner->sub_domain . "/finance",
            "type" => notificationType('Info'),
        ]);

        $topic = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound = config('sheba.push_notification_sound.manager');

        (new PushNotificationHandler())->send([
            "title" => 'ওয়ালেট ওয়ার্নিং!',
            "message" => $notification,
            "event_type" => 'WalletWarning',
            "sound" => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel, $sound);
    }

    public function updateRewardPoint(Partner $partner, $point)
    {
        $new_reward_point = $partner->reward_point + $point;
        $this->update($partner, ['reward_point' => $new_reward_point]);
    }
}
