<?php namespace Sheba\Subscription\Partner;

use App\Jobs\PartnerRenewalSMS;
use App\Models\Partner;
use App\Models\PartnerStatusChangeLog;
use App\Models\PartnerSubscriptionPackage;
use App\Models\Tag;
use App\Repositories\NotificationRepository;
use App\Repositories\SmsHandler;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use App\Sheba\Subscription\Partner\PartnerSubscriptionChange;
use App\Sheba\Subscription\Partner\PartnerSubscriptionCharges;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;
use ReflectionException;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\InvalidSourceException;
use Sheba\AccountingEntry\Exceptions\KeyNotFoundException;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\ModificationFields;
use Sheba\Partner\PartnerStatuses;
use Sheba\PartnerWallet\PartnerTransactionHandler;
use Sheba\PartnerWallet\PaymentByBonusAndWallet;
use Sheba\Subscription\Exceptions\InvalidPreviousSubscriptionRules;
use Sheba\Subscription\SubscriptionPackage;

class PartnerSubscriptionBilling
{
    use ModificationFields;

    /** @var Partner $partner */
    public  $partner;
    public  $runningCycleNumber;
    private $partnerTransactionHandler;
    public  $partnerBonusHandler;
    public  $today;
    public  $refundAmount;
    public  $packagePrice;
    /**
     * @var PartnerSubscriptionPackage
     */
    public  $packageFrom;
    /**
     * @var PartnerSubscriptionPackage
     */
    public  $packageTo;
    private $isCollectAdvanceSubscriptionFee = false;
    public  $packageOriginalPrice;
    public  $adjustedCreditFromLastSubscription;
    public  $newBillingType;
    public  $oldBillingType;
    public  $discountId;
    private $notification = 1;
    /**
     * @var int
     */
    public $exchangeDaysToBeAdded = 0;

    private $wallet_transaction;

    /**
     * PartnerSubscriptionBilling constructor.
     *
     * @param Partner $partner
     */
    public function __construct(Partner $partner)
    {
        $this->partner                         = $partner;
        $this->partnerTransactionHandler       = new PartnerTransactionHandler($this->partner);
        $this->partnerBonusHandler             = new PaymentByBonusAndWallet($this->partner, $this->partner->subscription);
        $this->today                           = Carbon::today();
        $this->refundAmount                    = 0;
        $this->isCollectAdvanceSubscriptionFee = $this->partner->isAlreadyCollectedAdvanceSubscriptionFee();
    }

    public function getExchangedDays()
    {
        return $this->exchangeDaysToBeAdded;
    }

    public function setNotification($key)
    {
        $this->notification = $key;
        return $this;
    }

    public function runSubscriptionBilling()
    {
        $this->runningCycleNumber = $this->calculateRunningBillingCycleNumber();
        $this->packagePrice       = $this->getSubscribedPackageDiscountedPrice();
        $this->billingDatabaseTransactions($this->packagePrice);
        if (!$this->isCollectAdvanceSubscriptionFee) {
            (new PartnerSubscriptionCharges($this))->setPackage($this->partner->subscription, $this->partner->subscription, $this->partner->billing_type, $this->partner->billing_type)->shootLog(constants('PARTNER_PACKAGE_CHARGE_TYPES')[PartnerSubscriptionChange::RENEWED]);
        }
        dispatch((new PartnerRenewalSMS($this->partner))->setPackage($this->partner->subscription)->setSubscriptionAmount($this->packagePrice));
    }

    /**
     * @param SubscriptionPackage|PartnerSubscriptionPackage $old_package
     * @param SubscriptionPackage|PartnerSubscriptionPackage $new_package
     * @param                                                $old_billing_type
     * @param                                                $new_billing_type
     * @param                                                $discount_id
     * @return PartnerSubscriptionBilling
     * @throws Exception
     */
    public function runUpgradeBilling($old_package, $new_package, $old_billing_type, $new_billing_type, $discount_id)
    {
        $this->discountId     = $discount_id;
        $this->packageFrom    = $old_package;
        $this->oldBillingType = $old_billing_type;
        $this->packageTo      = $new_package;
        $this->newBillingType = $new_billing_type;
        $this->updateBillingInfo();
        $grade = $this->findGrade($new_package, $old_package, $new_billing_type, $old_billing_type);
        if (in_array($grade, [PartnerSubscriptionChange::DOWNGRADE, PartnerSubscriptionChange::UPGRADE]) || empty($this->partner->billing_start_date)) {
            $this->partner->billing_start_date = $this->today;
            $this->partner->save();
        }
        $this->billingDatabaseTransactions();
        if (!$this->isCollectAdvanceSubscriptionFee) {
            (new PartnerSubscriptionCharges($this))->setPackage($old_package, $new_package, $old_billing_type, $new_billing_type)->shootLog($grade);
        }
        if(isset($this->notification) && $this->notification === 1)
            $this->sendSmsForSubscriptionUpgrade($old_package, $new_package, $old_billing_type, $new_billing_type, $grade);
        $this->storeJournal();
        $this->storeEntry();
        return $this;
    }

    /**
     * @throws InvalidPreviousSubscriptionRules
     * @throws Exception
     */
    private function updateBillingInfo()
    {
        $discount = 0;
        if ($this->discountId) $discount = $this->packageTo->discountPriceFor($this->discountId);
        $this->adjustedCreditFromLastSubscription = $this->partner->periodicBillingHandler()->remainingCredit();
        $this->packageOriginalPrice               = !$this->isCollectAdvanceSubscriptionFee ? ($this->packageTo->originalPrice($this->newBillingType) - $discount) : $this->partner->alreadyCollectedSubscriptionFee();
        $this->packagePrice                       = $this->packageOriginalPrice;
//        if ($this->packagePrice < 0) {
//            $this->refundRemainingCredit(abs($this->packagePrice));
//            $this->packagePrice = 0;
//        }
        if ($this->adjustedCreditFromLastSubscription > 0 && $this->packageTo->originalPricePerDay($this->newBillingType) > 0)
            $this->exchangeDaysToBeAdded = ceil($this->adjustedCreditFromLastSubscription / $this->packageTo->originalPricePerDay($this->newBillingType));

    }


    private function calculateRunningBillingCycleNumber()
    {
        if (!$this->partner->billing_start_date) return 1;
        if ($this->partner->billing_type == BillingType::MONTHLY) {
            $diff     = $this->today->month - $this->partner->billing_start_date->month;
            $yearDiff = ($this->today->year - $this->partner->billing_start_date->year);
            return $diff + ($yearDiff * 12) + 1;
        } elseif ($this->partner->billing_type == BillingType::HALF_YEARLY) {
            $month_diff = $this->today->diffInMonths($this->partner->billing_start_date);
            return (int)($month_diff / 6) + 1;
        } elseif ($this->partner->billing_type == BillingType::YEARLY) {
            return ($this->today->year - $this->partner->billing_start_date->year) + 1;
        }
    }

    private function getSubscribedPackageDiscountedPrice()
    {
        /** @var PartnerSubscriptionPackage $partner_subscription */
        $partner_subscription = PartnerSubscriptionPackage::find($this->partner->package_id);
        $original_price       = $partner_subscription->originalPrice($this->partner->billing_type);
        $discount             = $this->calculateSubscribedPackageDiscount($this->runningCycleNumber, $original_price);
        return $original_price - $discount;
    }

    /**
     * @throws Exception
     */
    private function billingDatabaseTransactions()
    {
        $package_price = $this->packagePrice;
        DB::transaction(function () use ($package_price) {
            if (!$this->isCollectAdvanceSubscriptionFee) {
                $this->wallet_transaction = $this->partnerTransactionForSubscriptionBilling($package_price);
            }
            $this->partner->last_billed_date   = $this->today;
            $this->partner->last_billed_amount = $this->packageOriginalPrice;
            if ($this->partner->status == PartnerStatuses::INACTIVE) {
                $this->revokeStatus();
            }
            $this->partner->save();
        });
    }

    public function getWalletTransaction()
    {
        return $this->wallet_transaction;
    }

    private function revokeStatus()
    {
        $log                   = PartnerStatusChangeLog::query()->where([
            ['partner_id', $this->partner->id],
            ['reason', 'Subscription Expired'],
            ['to', PartnerStatuses::INACTIVE],
            ['from', '!=', PartnerStatuses::INACTIVE]
        ])->orderBy('created_at', 'DESC')->first();
        $this->partner->status = $log ? $log->from : 'Onboarded';
        $this->partner->statusChangeLogs()->create([
            'from'            => $log ? $log->to : 'Inactive',
            'to'              => $log ? $log->from : 'Onboarded',
            'reason'          => 'Subscription Revoked',
            'log'             => 'Partner became active due to subscription purchase',
            'created_by'      => 'automatic',
            'created_by_name' => 'automatic',
            'created_at'      => Carbon::now()
        ]);
    }

    /**
     * @param $package_price
     * @throws Exception
     */
    private function advanceBillingDatabaseTransactions($package_price)
    {
        DB::transaction(function () use ($package_price) {
            $this->partnerTransactionForSubscriptionBilling($package_price);
        });
    }

    /**
     * @param $package_price
     * @return Model|null
     * @throws Exception
     */
    private function partnerTransactionForSubscriptionBilling($package_price)
    {
        $package_details = $this->packageTo->name ." - ". $this->newBillingType;
        $package_price=round($package_price,2);
        $package_price = number_format($package_price, 2, '.', '');
        return $this->partnerBonusHandler->pay($package_price, "%d BDT has been deducted for subscription package ($package_details)", [$this->getSubscriptionTag()->id]);
    }

    /**
     * @param $refund_amount
     * @throws Exception
     */
    private function refundRemainingCredit($refund_amount)
    {
        $refund_amount = number_format($refund_amount, 2, '.', '');
        $this->partnerTransactionHandler->credit($refund_amount, $refund_amount . ' BDT has been refunded due to subscription package upgrade', null, [$this->getSubscriptionTag()->id]);
        $this->refundAmount = $refund_amount;

    }

    /**
     * @param $running_bill_cycle_no
     * @param $original_price
     * @return float|int
     */
    private function calculateSubscribedPackageDiscount($running_bill_cycle_no, $original_price)
    {
        if ($this->partner->discount_id) {
            $subscription_discount   = $this->partner->subscriptionDiscount;
            $discount_billing_cycles = json_decode($subscription_discount->applicable_billing_cycles);
            if (empty($discount_billing_cycles) || in_array($running_bill_cycle_no, $discount_billing_cycles)) {
                if ($subscription_discount->is_percentage) {
                    return $original_price * ($subscription_discount->amount / 100);
                } else {
                    return (double)$subscription_discount->amount;
                }
            }
        }
        return 0;
    }

    private function getSubscriptionTag()
    {
        return Tag::where('name', 'Subscription fee')->where('taggable_type', 'App\\Models\\PartnerTransaction')->first();
    }

    /**
     * @param PartnerSubscriptionPackage $old_package
     * @param PartnerSubscriptionPackage $new_package
     * @param                            $old_billing_type
     * @param                            $new_billing_type
     * @param string                     $grade
     * @throws Exception
     */
    private function sendSmsForSubscriptionUpgrade(PartnerSubscriptionPackage $old_package, PartnerSubscriptionPackage $new_package, $old_billing_type, $new_billing_type, $grade = PartnerSubscriptionChange::UPGRADE)
    {
        if ((int)constants('PARTNER_SUBSCRIPTION_SMS') == 1) {
            $template = null;
            if ($grade == PartnerSubscriptionChange::UPGRADE) {
                $template = 'upgrade-subscription';
            } elseif ($grade == PartnerSubscriptionChange::RENEWED) {
                $template = 'renew-subscription';
            } elseif ($grade == PartnerSubscriptionChange::DOWNGRADE) {
                $template = 'downgrade-subscription';
            }
            if ($template) {
                self::sendSms($this->partner, $old_package, $new_package, $old_billing_type, $new_billing_type, $this->packagePrice, $template);

                if($new_package->id !== PeriodicBillingHandler::FREE_PACKAGE_ID)
                    self::sendNotification($this->partner, $old_package, $new_package, $old_billing_type, $new_billing_type, $this->packagePrice, $grade);
            }
        }
    }

    /**
     * @param PartnerSubscriptionPackage $new
     * @param PartnerSubscriptionPackage $old
     * @param $new_billing_type
     * @param $old_billing_type
     * @return string
     */
    public function findGrade($new, $old, $new_billing_type, $old_billing_type)
    {
        $new_price    = $new->originalPrice();
        $old_price    = $old->originalPrice();
        if ($old_price < $new_price) {
            return PartnerSubscriptionChange::UPGRADE;
        } else if ($old_price > $new_price) {
            return PartnerSubscriptionChange::DOWNGRADE;
        } else {
            $old_type_duration = $old->originalDuration($old_billing_type);
            $new_type_duration = $new->originalDuration($new_billing_type);
            if ($old_type_duration < $new_type_duration) {
                return PartnerSubscriptionChange::UPGRADE;
            } elseif ($old_type_duration > $new_type_duration) {
                return PartnerSubscriptionChange::DOWNGRADE;
            } else {
                return PartnerSubscriptionChange::RENEWED;
            }
        }
    }

    /**
     * @param Partner $partner
     * @param         $old_package
     * @param         $new_package
     * @param         $old_billing_type
     * @param         $new_billing_type
     * @param         $price
     * @param         $grade
     */
    public static function sendNotification(Partner $partner, $old_package, $new_package, $old_billing_type, $new_billing_type, $price, $grade)
    {
        $title     = '';
        $message   = '';
        $type_text = $new_package->titleTypeBn($new_billing_type);
        $fee       = convertNumbersToBangla(floatval($price));
        switch ($grade) {
            case PartnerSubscriptionChange::UPGRADE:
                $title   = "সাবস্ক্রিপশন সম্পন্ন";
                $message = " আপনি এসম্যানেজার এর $type_text $new_package->show_name_bn প্যকেজ এ সফল ভাবে সাবস্ক্রিপশন সম্পন্ন করেছেন। সাবস্ক্রিপশন ফি বাবদ $fee  টাকা চার্জ করা হয়েছে। **সাবক্রিপশন এর সাথে 5% ভ্যাট অন্তর্ভুক্ত ";
                break;
            case PartnerSubscriptionChange::RENEWED:
                $title   = "সাবস্ক্রিপশন  নবায়ন";
                $message = "আপনি এসম্যানেজার এর $type_text $new_package->show_name_bn প্যকেজ এ সফল ভাবে সাবস্ক্রিপশন নবায়ন করেছেন। সাবস্ক্রিপশন ফি বাবদ $fee টাকা চার্জ করা হয়েছে। **সাবক্রিপশন এর সাথে 5% ভ্যাট অন্তর্ভুক্ত ";
                break;
            case PartnerSubscriptionChange::DOWNGRADE:
                return;
        }
        (new NotificationRepository())->sendSubscriptionNotification($title, $message, $partner);
    }

    /**
     * @param Partner $partner
     * @param         $old_package
     * @param         $new_package
     * @param         $old_billing_type
     * @param         $new_billing_type
     * @param         $price
     * @param         $template
     * @throws Exception
     */
    public static function sendSms(Partner $partner, $old_package, $new_package, $old_billing_type, $new_billing_type, $price, $template)
    {
        (new SmsHandler($template))
            ->setBusinessType(BusinessType::SMANAGER)
            ->setFeatureType(FeatureType::PARTNER_SUBSCRIPTION)
            ->send($partner->getContactNumber(), [
                'old_package_name'       => $old_package->show_name_bn,
                'new_package_name'       => $new_package->show_name_bn,
                'subscription_amount'    => $price,
                'old_package_type'       => $old_billing_type,
                'new_package_type'       => $new_billing_type,
                'package_name'           => $new_package->show_name_bn,
                'formatted_package_type' => $new_package->titleTypeBn($new_billing_type),
                'package_type'           => $new_billing_type
            ]);
    }

    /**
     * @throws ExpenseTrackingServerError
     */
    private function storeEntry()
    {
        /**
         * Expense Entry for subscription
         *
         * @var AutomaticEntryRepository $entry
         */
        $entry = app(AutomaticEntryRepository::class);
        $entry->setPartner($this->partner)->setHead(AutomaticExpense::SUBSCRIPTION_FEE)->setAmount($this->packagePrice)->store();
    }

    /**
     * @throws ReflectionException
     * @throws AccountingEntryServerError
     * @throws InvalidSourceException|KeyNotFoundException
     */
    private function storeJournal()
    {
        $transaction = $this->getWalletTransaction();
        if(isset($transaction)) {
            (new JournalCreateRepository())->setTypeId($this->partner->id)->setSource($transaction)
                ->setAmount($transaction->amount)->setDebitAccountKey((new Accounts())->expense->subscription_purchase::SUBSCRIPTION_PURCHASE)
                ->setCreditAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
                ->setDetails("Subscription purchase")->setReference("Package changed from ".$this->packageFrom->id. " to ".$this->packageTo->id)
                ->store();
        }
    }
}
