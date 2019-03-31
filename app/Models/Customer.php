<?php namespace App\Models;

use App\Sheba\Payment\Rechargable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Sheba\MovieTicket\MovieAgent;
use Sheba\MovieTicket\MovieTicketCommission;
use Sheba\MovieTicket\MovieTicketTrait;
use Sheba\MovieTicket\MovieTicketTransaction;
use Sheba\Payment\Wallet;
use Sheba\Reward\Rewardable;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpTrait;
use Sheba\TopUp\TopUpTransaction;
use Sheba\Voucher\VoucherCodeGenerator;

class Customer extends Authenticatable implements Rechargable, Rewardable, TopUpAgent, MovieAgent
{
    use TopUpTrait;
    use MovieTicketTrait;
    use Wallet;

    protected $fillable = ['name', 'mobile', 'email', 'password', 'fb_id', 'mobile_verified', 'email_verified', 'address', 'gender', 'dob', 'pro_pic', 'wallet', 'created_by', 'created_by_name', 'updated_by', 'updated_by_name', 'remember_token', 'reference_code', 'referrer_id', 'profile_id'];
    protected $hidden = ['password', 'remember_token',];
    protected $casts = ['wallet' => 'double'];
    private $firstOrder;

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

    public function suggestedPromotion()
    {
        return suggestedVoucherFor($this);
    }

    public function generateReferral()
    {
        return VoucherCodeGenerator::byName($this->profile->name);
    }

    public function vouchers()
    {
        return $this->morphMany(Voucher::class, 'owner');
    }

    public function usedVouchers()
    {
        return $this->orders()->where('voucher_id', '<>', null)->get()->map(function ($order) {
            return $order->voucher;
        })->unique();
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
        $this->debitWallet($transaction->getAmount());
        $wallet_transaction_data = [
            'event_type' => get_class($transaction->getTopUpOrder()),
            'event_id' => $transaction->getTopUpOrder()->id,
            'amount' => $transaction->getAmount(),
            'type' => 'Debit',
            'log' => $transaction->getLog()
        ];

        $this->walletTransaction($wallet_transaction_data);
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
        $this->debitWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Debit', 'log' => $transaction->getLog()]);
    }
}
