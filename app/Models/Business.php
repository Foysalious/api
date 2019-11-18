<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\Payment\PayableUser;
use Sheba\Payment\Wallet;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpTrait;
use Sheba\TopUp\TopUpTransaction;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class Business extends Model implements TopUpAgent, PayableUser, HasWalletTransaction
{
    use Wallet, ModificationFields, TopUpTrait;

    protected $guarded = ['id'];

    public function members()
    {
        return $this->belongsToMany(Member::class)->withTimestamps();
    }

    public function businessSms()
    {
        return $this->hasMany(BusinessSmsTemplate::class);
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class, 'business_partners');
    }

    public function deliveryAddresses()
    {
        return $this->hasMany(BusinessDeliveryAddress::class);
    }

    public function bankInformations()
    {
        return $this->hasMany(BusinessBankInformations::class);
    }

    public function joinRequests()
    {
        return $this->morphMany(JoinRequest::class, 'organization');
    }

    public function businessCategory()
    {
        return $this->belongsTo(BusinessCategory::class);
    }

    public function businessTrips()
    {
        return $this->hasMany(BusinessTrip::class);
    }

    public function businessTripRequests()
    {
        return $this->hasMany(BusinessTripRequest::class);
    }

    public function bonusLogs()
    {
        return $this->morphMany(BonusLog::class, 'user');
    }

    public function topups()
    {
        return $this->hasMany(TopUpOrder::class, 'agent_id')->where('agent_type', 'App\\Models\\Business');
    }

    public function shebaCredit()
    {
        return $this->wallet + $this->shebaBonusCredit();
    }

    public function shebaBonusCredit()
    {
        return (double)$this->bonuses()->where('status', 'valid')->sum('amount');
    }

    public function bonuses()
    {
        return $this->morphMany(Bonus::class, 'user');
    }

    public function transactions()
    {
        return $this->hasMany(BusinessTransaction::class);
    }

    public function vehicles()
    {
        return $this->morphMany(Vehicle::class, 'owner');
    }

    public function businessSmsTemplates()
    {
        return $this->hasMany(BusinessSmsTemplate::class, 'business_id');
    }

    public function procurements()
    {
        return $this->morphMany(Procurement::class, 'owner');
    }

    public function hiredVehicles()
    {
        return $this->morphMany(HiredVehicle::class, 'hired_by');
    }

    public function hiredDrivers()
    {
        return $this->morphMany(HiredDriver::class, 'hired_by');
    }

    public function getCommission()
    {
        return new \Sheba\TopUp\Commission\Business();
    }

    public function topUpTransaction(TopUpTransaction $transaction)
    {
        /**
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->debitWallet($transaction->getAmount());
         * $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Debit', 'log' => $transaction->getLog()]);*/
        (new WalletTransactionHandler())
            ->setModel($this)
            ->setAmount($transaction->getAmount())
            ->setSource(TransactionSources::TOP_UP)
            ->setType('debit')
            ->setLog($transaction->getLog())
            ->dispatch();
    }

    public function getMobile()
    {
        return '+8801678242934';
    }

    public function getContactPerson()
    {
        if ($super_admin = $this->getAdmin()) return $super_admin->profile->name;
        return null;
    }

    public function getAdmin()
    {
        if ($super_admin = $this->superAdmins()->first()) return $super_admin;
        return null;
    }

    public function superAdmins()
    {
        return $this->belongsToMany(Member::class)->where('is_super', 1);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
