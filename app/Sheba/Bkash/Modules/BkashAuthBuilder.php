<?php namespace Sheba\Bkash\Modules;

use App\Models\Affiliate;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\Payable;
use Exception;
use Sheba\Payment\Methods\Bkash\Stores\BkashDefaultStore;
use Sheba\Payment\Methods\Bkash\Stores\BkashDynamicStore;
use Sheba\Payment\Methods\Bkash\Stores\BkashMarketplaceStore;
use Sheba\Payment\Methods\Bkash\Stores\BkashStore;

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
    public static function getForUserAndType($user, $type)
    {
        if ($type == 'payment_link') return self::set017BkashAuth();
        if ($user instanceof Customer || $user instanceof Business) {
            return self::set018BkashAuth();
        } elseif ($user instanceof Affiliate) {
            return self::set017BkashAuth();
        } elseif ($user instanceof Partner) {
            return self::set017BkashAuth();
        } else {
            throw new Exception('Invalid User Type');
        }
    }
    /**
     *
     * @return BkashStore
     * @throws Exception
     */
    public static function getStore(Payable $payable)
    {
        $type=$payable->type;
        $user=$payable->user;
        if ($type == 'payment_link') return (new BkashDynamicStore())->setPayable($payable)->setBkashAuth();
        if ($user instanceof Customer || $user instanceof Business) {
            return (new BkashMarketplaceStore());
        } elseif ($user instanceof Affiliate) {
            return (new BkashDefaultStore());
        } elseif ($user instanceof Partner) {
            return (new BkashDefaultStore());
        } else {
            throw new Exception('Invalid User Type');
        }
    }

    public static function sManagerStore(){
        return self::generateBkashAuth('sManager');
    }
    public static function marketplaceStore(){
        return self::generateBkashAuth('marketplace');
    }
}
