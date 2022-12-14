<?php namespace Sheba\Bkash\Modules;

use App\Models\Affiliate;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Partner;
use Exception;
use Sheba\Payment\Methods\Bkash\BkashCredentialDto;

class BkashAuthBuilder
{
    /**
     * @return BkashAuth
     */
    public static function set017BkashAuth()
    {
        return self::generateBkashAuth('01799444000');
    }

    /**
     * @return BkashAuth
     */
    public static function set018BkashAuth()
    {
        return self::generateBkashAuth('01833922030');
    }

    /**
     * @return BkashAuth
     */
    public static function setTokenizedBkashAuth()
    {
        return self::generateBkashAuth('tokenized');
    }

    /**
     * @param $bkash_number
     * @return BkashAuth
     */
    private static function generateBkashAuth($bkash_number)
    {
        $bkash_auth = new BkashAuth();
        $bkash_auth->setKey(config("bkash.$bkash_number.app_key"))
            ->setSecret(config("bkash.$bkash_number.app_secret"))
            ->setUsername(config("bkash.$bkash_number.username"))
            ->setPassword(config("bkash.$bkash_number.password"))
            ->setUrl(config("bkash.$bkash_number.url"))
            ->setMerchantNumber($bkash_number);

        return $bkash_auth;
    }

    /**
     * @param $user
     * @param $type
     * @return BkashAuth
     * @throws Exception
     */
    public static function getForUserAndType(BkashCredentialDto $credential_dto): BkashAuth
    {
        if ($credential_dto->getUserType() == 'payment_link') return self::set017BkashAuth();
        $user = $credential_dto->getUser();
        if ($credential_dto->getTokenizedId()) {
            return self::setTokenizedBkashAuth()->setTokenizedId($credential_dto->getTokenizedId());
        } elseif ($user instanceof Customer || $user instanceof Business) {
            return self::set018BkashAuth();
        } elseif ($user instanceof Affiliate) {
            return self::set017BkashAuth();
        } elseif ($user instanceof Partner) {
            return self::set017BkashAuth();
        } else {
            throw new Exception('Invalid User Type');
        }
    }
}
