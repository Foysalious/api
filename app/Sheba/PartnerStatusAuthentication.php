<?php namespace Sheba;

use App\Models\Partner;
use Sheba\Authentication\Exceptions\AuthenticationFailedException;
use Sheba\Partner\PartnerStatuses;

class PartnerStatusAuthentication
{
    /**
     * @param Partner $partner
     * @throws AuthenticationFailedException
     */
    public function handleInside(Partner $partner)
    {
        self::generateException($partner->status);
    }

    /**
     * @throws AuthenticationFailedException
     */
    public static function generateException($status)
    {
        if($status === PartnerStatuses::BLACKLISTED)
            throw new AuthenticationFailedException("আপনাকে sManager থেকে স্থায়ী ভাবে বরখাস্ত করা হয়েছে। আরও জানতে কল করুন ১৬৫১৬।");

        elseif ($status === PartnerStatuses::PAUSED)
            throw new AuthenticationFailedException("আপনাকে sManager থেকে সাময়িক ভাবে বরখাস্ত করা হয়েছে। আরও জানতে কল করুন ১৬৫১৬।", 403);

    }
}
