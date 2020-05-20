<?php namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Sheba\Dal\BaseModel;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Payment\Wallet;
use Sheba\ProfileTrait;
use Sheba\Reward\Rewardable;
use Sheba\TopUp\TopUpTransaction;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class Resource extends BaseModel implements Rewardable, HasWalletTransaction
{
    use ProfileTrait, Wallet;

    protected $guarded = ['id'];
    protected $dates = ['verified_at'];
    protected $with = ['profile'];
    protected $casts = ['reward_point' => 'double', 'wallet' => 'double'];


    public function partners()
    {
        return $this->belongsToMany(Partner::class)->withPivot('resource_type');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function partnerResources()
    {
        return $this->hasMany(PartnerResource::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function employments()
    {
        return $this->hasMany(ResourceEmployment::class);
    }

    public function nocRequests()
    {
        return $this->hasMany(NocRequest::class);
    }

    public function transactions()
    {
        return $this->hasMany(PartnerTransaction::class);
    }

    public function topUpTransaction(TopUpTransaction $transaction)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->debitWallet($transaction->getAmount());
        $this->walletTransaction(['amount' => $transaction->getAmount(), 'type' => 'Debit', 'log' => $transaction->getLog()]);*/
        (new WalletTransactionHandler())->setModel($this)->setSource(TransactionSources::TOP_UP)
            ->setType('debit')->setAmount($transaction->getAmount())->setLog($transaction->getLog())
            ->dispatch();
    }

    public function associatePartners()
    {
        return $this->partners->unique();
    }

    public function typeIn($partner)
    {
        $partner = $partner instanceof Partner ? $partner->id : $partner;
        $types = [];
        foreach ($this->partners()->where('partner_id', $partner)->get() as $unique_partner) {
            $types[] = $unique_partner->pivot->resource_type;
        }
        return $types;
    }

    public function isOfTypesIn(Partner $partner, $types)
    {
        return boolval(count(array_intersect($types, $this->typeIn($partner))));
    }

    public function isManager(Partner $partner)
    {
        return $this->isOfTypesIn($partner, ["Admin", "Operation", "Owner"]);
    }

    public function isAdmin(Partner $partner)
    {
        return $this->isOfTypesIn($partner, ["Admin", "Owner"]);
    }

    public function isHandyman(Partner $partner)
    {
        return $this->isOfTypesIn($partner, ["Handyman"]);
    }

    public function categoriesIn($partner)
    {
        $partner = $partner instanceof Partner ? $partner->id : $partner;
        $categories = [];
        foreach ($this->partnerResources()->where('partner_id', $partner)->get() as $partner_resource) {
            foreach ($partner_resource->categories as $item) {
                array_push($categories, $item);
            }
        }
        return collect($categories)->unique('id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function rating()
    {
        return (!$this->reviews->isEmpty()) ? $this->reviews->avg('rating') : 0;
    }

    public function scopeVerified($query)
    {
        return $query->where('resources.is_verified', 1);
    }

    public function scopeUnverified($query)
    {
        return $query->where('resources.is_verified', 0);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Scope a query to only include resource of a given status.
     *
     * @param Builder $query
     * @param $status
     * @return Builder
     */
    public function scopeTrainingStatus($query, $status)
    {
        $query->where('is_trained', $status);
    }

    public function schedules()
    {
        return $this->hasMany(ResourceSchedule::class);
    }

    public function totalWalletAmount()
    {
        return $this->wallet;
    }

}
