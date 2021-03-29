<?php namespace Sheba\Subscription\Partner;


use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerSubscriptionUpdateRequest;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;
use Sheba\Subscription\Exceptions\AlreadyRunningSubscriptionRequestException;
use Sheba\Subscription\Exceptions\HasAlreadyCollectedFeeException;
use Sheba\Subscription\Exceptions\InvalidPreviousSubscriptionRules;

class PurchaseHandler
{
    use ModificationFields;

    /** @var Partner $partner */
    private $partner;
    /** @var PartnerSubscriptionPackage $newPackage */
    private $newPackage;
    private $newBillingType;
    private $modifier;
    private $grade;
    /** @var PartnerSubscriptionPackage $currentPackage */
    private $currentPackage;
    private $currentBillingType;
    /**
     * @var mixed|null
     */
    private $newPackagePrice;
    /**
     * @var mixed|null
     */
    private $runningDiscount;
    /**
     * @var array
     */
    private $balance;
    /**
     * @var PartnerSubscriptionUpdateRequest
     */
    private $newSubscriptionRequest;

    public function __construct(Partner $partner)
    {
        $this->setPartner($partner);
        $this->setCurrent();
    }

    /**
     * @param Partner $partner
     * @return PurchaseHandler
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param PartnerSubscriptionPackage $newPackage
     * @return PurchaseHandler
     */
    public function setNewPackage(PartnerSubscriptionPackage $newPackage)
    {
        $this->newPackage = $newPackage;
        return $this;
    }

    /**
     * @param mixed $newBillingType
     * @return PurchaseHandler
     */
    public function setNewBillingType($newBillingType)
    {
        $this->newBillingType = $newBillingType;
        return $this;
    }

    /**
     * @return string
     */
    public function getGrade()
    {
        $this->grade = $this->partner->subscriber()->getBilling()->findGrade($this->newPackage, $this->currentPackage, $this->newBillingType, $this->currentBillingType);
        return $this->grade;
    }

    /**
     * @param mixed $modifier
     * @return PurchaseHandler
     */
    public function setConsumer($modifier)
    {
        $this->modifier = $modifier;
        $this->setModifier($modifier);
        return $this;
    }

    private function setCurrent()
    {
        $this->currentPackage     = $this->partner->subscription;
        $this->currentBillingType = $this->partner->billing_type;
    }


    /**
     * @throws HasAlreadyCollectedFeeException
     */
    public function checkIfAlreadyCollected()
    {
        if ($this->partner->alreadyCollectedSubscriptionFee())
            throw new HasAlreadyCollectedFeeException();
    }


    /**
     * @throws AlreadyRunningSubscriptionRequestException|HasAlreadyCollectedFeeException
     */
    public function checkIfRunningAndAlreadyCollected()
    {
        if ($this->currentPackage->id == $this->newPackage->id && $this->newBillingType == $this->currentBillingType)
            throw new AlreadyRunningSubscriptionRequestException("আপনি বর্তমানে {$this->currentPackage->name_bn} প্যকেজ ব্যবহার করছেন ,আপনার বর্তমান প্যকেজ এর মেয়াদ শেষ হলে স্বয়ংক্রিয়  ভাবে নবায়ন হয়ে যাবে");
        if ($this->partner->alreadyCollectedSubscriptionFee())
            throw new HasAlreadyCollectedFeeException();
    }

    /**
     * @return PartnerSubscriptionUpdateRequest
     */
    public function getSubscriptionRequest()
    {
        $this->runningDiscount        = $this->newPackage->runningDiscount($this->newBillingType);
        $data                         = [
            'partner_id'       => $this->partner->id,
            'old_package_id'   => $this->currentPackage->id ?: 1,
            'new_package_id'   => $this->newPackage->id ?: 1,
            'old_billing_type' => $this->currentBillingType ?: 'monthly',
            'new_billing_type' => $this->newBillingType ?: 'monthly',
            'discount_id'      => $this->runningDiscount ? $this->runningDiscount->id : null
        ];
        $this->newSubscriptionRequest = PartnerSubscriptionUpdateRequest::create($this->withCreateModificationField($data));
        return $this->newSubscriptionRequest;
    }

    /**
     * @return bool
     * @throws InvalidPreviousSubscriptionRules
     */
    public function hasCredit()
    {
        $hasCredit     = $this->partner->hasCreditForSubscription($this->newPackage, $this->newBillingType);
        $this->balance = [
            'remaining_balance' => $this->partner->totalCreditForSubscription,
            'price'             => $this->partner->totalPriceRequiredForSubscription,
            'breakdown'         => $this->partner->creditBreakdown
        ];
        return $hasCredit;
    }

    public function getRequiredBalance()
    {
        return $this->partner->totalPriceRequiredForSubscription - $this->partner->totalCreditForSubscription;
    }

    /**
     * @throws \Exception
     */
    public function purchase()
    {
        $this->partner->subscriptionUpgrade($this->newPackage, $this->newSubscriptionRequest);
    }

    public function notifyForInsufficientBalance()
    {
        app(NotificationRepository::class)->sendInsufficientNotification($this->partner, $this->newPackage, $this->newBillingType, $this->grade);
    }

    /**
     * @param bool $new
     * @return array
     */
    public function getBalance($new = false)
    {
        if($new) {
            $data = array_merge($this->balance, ['remaining_balance' => $this->partner->wallet - $this->balance['breakdown']['threshold']]);
            return array_merge($data, ["subscription_package" => $this->partner->currentSubscription()->show_name_bn, "package_type" => $this->getSubscriptionFee(), "extended_days" => $this->extended_days()]);
        }
        return $this->balance;
    }

    public function getSubscriptionFee()
    {
        $fees = (json_decode($this->partner->currentSubscription()->new_rules)->subscription_fee);
        foreach ($fees as $fee)
            if($fee->title == $this->partner->billing_type) return $fee;

        return null;
    }

    public function extended_days()
    {
        $charges = $this->partner->subscriptionPackageCharges()->orderBy('created_at', 'desc')->first();
        return ($charges) ? $charges->adjusted_days_from_last_subscription : 0;
    }
}
