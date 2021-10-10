<?php namespace App\Models;

use App\Models\Transport\TransportTicketOrder;
use App\Sheba\Payment\Rechargable;
use Sheba\Dal\Customer\Events\CustomerSaved;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Types;
use Sheba\Wallet\HasWallet;
use Sheba\MovieTicket\MovieAgent;
use Sheba\MovieTicket\MovieTicketTrait;
use Sheba\MovieTicket\MovieTicketTransaction;
use Sheba\Payment\PayableUser;
use Sheba\Wallet\Wallet;
use Sheba\Report\Updater\Customer as ReportUpdater;
use Sheba\Reward\Rewardable;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpTrait;
use Sheba\TopUp\TopUpTransaction;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Transport\Bus\BusTicketCommission;
use Sheba\Transport\TransportAgent;
use Sheba\Transport\TransportTicketTransaction;
use Sheba\Voucher\Contracts\CanApplyVoucher;
use Sheba\Voucher\VoucherGeneratorTrait;
use Sheba\Dal\InfoCall\InfoCall;

class Customer extends Authenticatable implements Rechargable, Rewardable, TopUpAgent, MovieAgent, TransportAgent, CanApplyVoucher, PayableUser, HasWalletTransaction, HasWallet
{
    use TopUpTrait, MovieTicketTrait, Wallet, ReportUpdater, VoucherGeneratorTrait;

    protected $fillable = ['name', 'mobile', 'email', 'password', 'fb_id', 'mobile_verified', 'email_verified', 'address', 'gender', 'dob', 'pro_pic', 'wallet', 'created_by', 'created_by_name', 'updated_by', 'updated_by_name', 'remember_token', 'reference_code', 'referrer_id', 'profile_id', 'has_rated_customer_app','is_completed'];
    protected $hidden = ['password', 'remember_token',];
    protected $casts = ['wallet' => 'double'];
    private $firstOrder;

    public static $savedEventClass = CustomerSaved::class;

    public function mobiles()
    {
        return $this->hasMany(CustomerMobile::class);
    }

    public function infoCalls()
    {
        return $this->hasMany(InfoCall::class);
    }

    public function delivery_addresses()
    {
        return $this->hasMany(CustomerDeliveryAddress::class);
    }

    public function addresses()
    {
        return $this->hasMany(CustomerDeliveryAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function customerReviews()
    {
        return $this->hasMany(CustomerReview::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function custom_orders()
    {
        return $this->hasMany(CustomOrder::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function vouchers()
    {
        return $this->morphMany(Voucher::class, 'owner');
    }

    public function movieTicketOrders()
    {
        return $this->morphMany(MovieTicketOrder::class, 'agent');
    }

    public function topups()
    {
        return $this->hasMany(TopUpOrder::class, 'agent_id')->where('agent_type', 'App\\Models\\Customer');
    }

    public function usedVouchers()
    {
        return Voucher::join('orders', 'orders.voucher_id', '=', 'vouchers.id')->where('orders.customer_id', $this->id)->groupBy('vouchers.id')->select('vouchers.*')->get();
    }

    public function nthOrders(...$n)
    {
        if (!count($n)) throw new \InvalidArgumentException('n is not valid.');
        if (is_array($n[0])) $n = array_pop($n);

        $counter = 0;

        $orders = $this->orders()->orderBy('id')->get()->filter(function ($order) use (&$counter, $n) {
            $counter++;
            return in_array($counter, $n);
        });

        return count($n) == 1 ? $orders->first() : $orders;
    }

    public function getReferralAttribute()
    {
        $vouchers = $this->vouchers;
        return $vouchers ? $vouchers->first() : null;
    }

    public function getIdentityAttribute()
    {
        if ($this->profile->name != '') {
            return $this->profile->name;
        } elseif ($this->profile->mobile) {
            return $this->profile->mobile;
        }
        return $this->profile->email;
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function pushSubscriptions()
    {
        return $this->morphMany(PushSubscription::class, 'subscriber');
    }

    public function favorites()
    {
        return $this->hasMany(CustomerFavorite::class);
    }

    public function partnerOrders()
    {
        return $this->hasManyThrough(PartnerOrder::class, Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(CustomerTransaction::class);
    }

    public function bonuses()
    {
        return $this->morphMany(Bonus::class, 'user');
    }

    public function bonusLogs()
    {
        return $this->morphMany(BonusLog::class, 'user');
    }

    public function shebaCredit()
    {
        return $this->wallet + $this->shebaBonusCredit();
    }

    public function shebaBonusCredit()
    {
        return (double)$this->bonuses()->where('status', 'valid')->sum('amount');
    }

    public function setFirstOrder(Order $order)
    {
        $this->firstOrder = $order;
    }

    public function getFirstOrder()
    {
        return $this->firstOrder ?: $this->nthOrders(1);
    }

    public function topUpTransaction(TopUpTransaction $transaction)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         *  $this->debitWallet($transaction->getAmount());
         $wallet_transaction_data = [
             'event_type' => get_class($transaction->getTopUpOrder()),
             'event_id' => $transaction->getTopUpOrder()->id,
             'amount' => $transaction->getAmount(),
             'type' => 'Debit',
             'log' => $transaction->getLog()
         ];

         $this->walletTransaction($wallet_transaction_data);*/
        (new WalletTransactionHandler())
            ->setModel($this)
            ->setAmount($transaction->getAmount())
            ->setLog($transaction->getLog())
            ->setType(Types::debit())
            ->setSource(TransactionSources::TOP_UP)
            ->dispatch(['event_id' => $transaction->getTopUpOrder()->id, 'event_type' => get_class($transaction->getTopUpOrder())]);
    }

    public function getCommission()
    {
        return new \Sheba\TopUp\Commission\Customer();
    }

    public function subscriptions()
    {
        return $this->hasMany(SubscriptionOrder::class);
    }

    public function getAgreementId()
    {
        return $this->profile->bkash_agreement_id;
    }

    public function getMovieTicketCommission()
    {
        return new \Sheba\MovieTicket\Commission\Customer();
    }

    public function movieTicketTransaction(MovieTicketTransaction $transaction)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->debitWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Debit', 'log' => $transaction->getLog()]);*/
        (new WalletTransactionHandler())->setModel($this)->setAmount($transaction->getAmount())->setType(Types::debit())->setSource(TransactionSources::MOVIE)->setLog($transaction->getLog())->dispatch();
    }

    public function movieTicketTransactionNew(MovieTicketTransaction $transaction)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->creditWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Credit', 'log' => $transaction->getLog()]);*/
        (new WalletTransactionHandler())->setModel($this)->setAmount($transaction->getAmount())->setType(Types::credit())->setSource(TransactionSources::MOVIE)->setLog($transaction->getLog())->dispatch();
    }

    public function transportTicketOrders()
    {
        return $this->morphMany(TransportTicketOrder::class, 'agent');
    }

    /**
     * @return BusTicketCommission|\Sheba\Transport\Bus\Commission\Customer
     */
    public function getBusTicketCommission()
    {
        return new \Sheba\Transport\Bus\Commission\Customer();
    }

    public function transportTicketTransaction(TransportTicketTransaction $transaction)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->debitWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'event_type' => $transaction->getEventType(), 'event_id' => $transaction->getEventId(), 'type' => 'Debit', 'log' => $transaction->getLog()]);*/
        (new WalletTransactionHandler())
            ->setModel($this)
            ->setAmount($transaction->getAmount())
            ->setType(Types::debit())
            ->setSource(TransactionSources::TRANSPORT)
            ->setLog($transaction->getLog())
            ->dispatch(['event_type' => $transaction->getEventType(), 'event_id' => $transaction->getEventId()]);
    }

    public function getMobile()
    {
        return $this->profile->mobile;
    }

    public function getName()
    {
        return $this->profile->name;
    }

    public function isCompleted()
    {
        $profile = $this->profile;
        return $profile->name && $profile->gender && $profile->dob;
    }
}
