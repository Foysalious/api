<?php
namespace App\Models;

use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\BaseModel;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use Sheba\Dal\Retailer\Retailer;
use Sheba\Dal\RetailerMembers\RetailerMember;
use Sheba\Dal\StrategicPartnerMember\StrategicPartnerMember;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Sheba\Payment\Factory\PaymentStrategy;

class Profile extends Model implements JWTSubject
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'bn_name',
        'mobile',
        'email',
        'password',
        'remember_token',
        'driver_id',
        'fb_id',
        'google_id',
        'mobile_verified',
        'email_verified',
        'address',
        'permanent_address',
        'blood_group',
        'post_office',
        'post_code',
        'mother_name',
        'father_name',
        'permanent_address',
        'bkash_agreement_id',
        'occupation',
        'nid_no',
        'nid_verified',
        'nid_verification_date',
        'passport_no',
        'passport_image',
        'last_nid_verification_request_date',
        'nid_verification_request_count',
        'nid_image_front',
        'nid_image_back',
        'tin_no',
        'tin_certificate',
        'gender',
        'nationality',
        'dob',
        'pro_pic',
        'birth_place',
        'total_asset_amount',
        'monthly_living_cost',
        'monthly_loan_installment_amount',
        'utility_bill_attachment',
        'nominee_id',
        'nominee_relation',
        'grantor_id',
        'grantor_relation',
        'portal_name',
        'created_by',
        'nid_issue_date',
        'birth_place',
        'created_by_name',
        'updated_by',
        'updated_by_name',
        'created_at',
        'updated_at',
    ];

    public function customer() {
        return $this->hasOne(Customer::class);
    }

    public function resource() {
        return $this->hasOne(Resource::class);
    }

    public function affiliate() {
        return $this->hasOne(Affiliate::class);
    }

    public function member() {
        return $this->hasOne(Member::class);
    }

    public function driver() {
        return $this->belongsTo(Driver::class);
    }

    public function joinRequests() {
        return $this->hasMany(JoinRequest::class);
    }

    public function posCustomer() {
        return $this->hasOne(PosCustomer::class);
    }

    public function getIdentityAttribute() {
        if ($this->name != '') {
            return $this->name;
        } elseif ($this->mobile) {
            return $this->mobile;
        }
        return $this->email;
    }

    public function banks() {
        return $this->hasMany(ProfileBankInformation::class);
    }

    public function mobileBanks() {
        return $this->hasMany(ProfileMobileBankInformation::class);
    }

    public function nominee() {
        return $this->hasOne(Profile::class, 'id', 'nominee_id');
    }

    public function granter() {
        return $this->hasOne(Profile::class, 'id', 'grantor_id');
    }

    public function retailers() {
        return $this->hasMany(Retailer::class, 'mobile', 'mobile');
    }

    public function bankUser() {
        return $this->hasOne(BankUser::class);
    }

    public function StrategicPartnerMember() {
        return $this->hasOne(StrategicPartnerMember::class);
    }

    public function isBlackListed() {
        return (int)$this->is_blacklisted;
    }
    public function searchOtherUsingNid($nid){
        return self::where('nid_no',$nid)->where('id','!=',$this->id)->first();
    }

    public function searchOtherUsingVerifiedNid($nid)
    {
        return self::where('nid_no',$nid)->where('id','!=',$this->id)->where('nid_verified', 1)->first();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return ProfileFactory
     */
    protected static function newFactory(): ProfileFactory
    {
        return new ProfileFactory();
    }

    public function getAgreementId($method)
    {
        if ($method == PaymentStrategy::BKASH) return $this->bkash_agreement_id;
    }
}
