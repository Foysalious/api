<?php namespace Sheba\Affiliate;

use App\Http\Validators\MobileNumberValidator;
use App\Models\Affiliate;
use App\Models\Profile;
use App\Repositories\NotificationRepository;
use App\Repositories\SmsHandler;
use DB;
use Sheba\Repositories\AffiliateRepository;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class Creator
{
    /** @var Profile $profile */
    private $profile;
    /** @var AffiliateRepository $affiliateRepo */
    private $affiliateRepo;
    /** @var Affiliate $affiliate */
    private $affiliate;
    private $affiliateBonusAmount;
    /** @var WalletTransactionHandler $walletTransactionHandler */
    private $walletTransactionHandler;

    private $geolocation;


    /**
     * @param $geolocation
     * @return $this
     */
    public function setGeolocation($geolocation)
    {
        $this->geolocation = $geolocation;
        return $this;
    }

    public function __construct(AffiliateRepository $affiliate_repo, WalletTransactionHandler $wallet_transaction_handler)
    {
        $this->affiliateRepo            = $affiliate_repo;
        $this->affiliateBonusAmount     = constants('AFFILIATION_REGISTRATION_BONUS');
        $this->walletTransactionHandler = $wallet_transaction_handler;
    }

    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;
        return $this;
    }

    public function create()
    {
        $status = $this->profile->nid_verified == 1 ? VerificationStatus::VERIFIED : VerificationStatus::PENDING;

        $data = [
            'profile_id' => $this->profile->id,
            'remember_token' => randomString(255, false, true),
            'verification_status' => $status,
            'geolocation' => $this->geolocation
        ];
        $this->affiliate = $this->affiliateRepo->setModel(new Affiliate())->create($data);
        if (constants('AFFILIATION_REGISTRATION_BONUS') > 0) $this->registrationBonus();
        (new NotificationRepository())->forAffiliateRegistration($this->affiliate);
        $this->affiliateRepo->makeAmbassador($this->affiliate);
    }

    private function registrationBonus()
    {
        $this->storeBonusAmount();
        if ((new MobileNumberValidator())->validateBangladeshi($this->profile->mobile)) $this->sendSms();
    }

    private function storeBonusAmount()
    {
        DB::transaction(function () {
            $log = "Affiliate earned $this->affiliateBonusAmount point for registration";
            $this->walletTransactionHandler->setModel($this->affiliate)->setType(Types::credit())->setAmount($this->affiliateBonusAmount)->setLog($log)->store();
            $this->affiliateRepo->update($this->affiliate, ['acquisition_cost' => $this->affiliateBonusAmount]);
        });
    }

    private function sendSms()
    {
        (new SmsHandler('affiliate-register'))->send($this->profile->mobile, [
            'bonus_amount' => $this->affiliateBonusAmount
        ]);
    }
}
