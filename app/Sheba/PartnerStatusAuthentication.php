<?php namespace Sheba;

use App\Models\Partner;
use App\Models\Resource as ReSrc;
use Sheba\Authentication\Exceptions\AuthenticationFailedException;
use Sheba\OAuth2\AccountServerClient;
use Sheba\Partner\PartnerStatuses;

class PartnerStatusAuthentication
{
    /**
     * @param Partner $partner
     * @throws AuthenticationFailedException
     */
    public function handleInside(Partner $partner)
    {
        $this->logoutForBlacklisted($partner);
        self::generateException($partner->status);
    }

    /**
     * @throws AuthenticationFailedException
     */
    public static function generateException($status)
    {
        if($status === PartnerStatuses::BLACKLISTED) {
            throw new AuthenticationFailedException("আপনাকে sManager থেকে স্থায়ী ভাবে বরখাস্ত করা হয়েছে। আরও জানতে কল করুন ১৬৫১৬।");
        }

        elseif ($status === PartnerStatuses::PAUSED)
            throw new AuthenticationFailedException("আপনাকে sManager থেকে সাময়িক ভাবে বরখাস্ত করা হয়েছে। আরও জানতে কল করুন ১৬৫১৬।", 403);

    }

    /**
     * @param ReSrc $resource
     */
    public function logoutFromAllDevices(ReSrc $resource)
    {
        (app(AccountServerClient::class))->post("/api/v1/logout-from-all/admin", ["resource_id" => $resource->id]);
    }

    /**
     * @param Partner $partner
     */
    public function logoutForBlacklisted(Partner $partner)
    {
        try {
            /** @var PartnerStatusAuthentication $partnerStatus */
            $partnerStatus = app()->make(PartnerStatusAuthentication::class);
            if ($partner->status === PartnerStatuses::BLACKLISTED) {
                $partner->resources->each(function ($resource) use ($partnerStatus) {
                    $partnerStatus->logoutFromAllDevices($resource);
                });
            }
        } catch (\Exception $e) {
            dd($e);
        }
    }
}
