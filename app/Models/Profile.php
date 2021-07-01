<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Retailer\Retailer;
use Sheba\Dal\RetailerMembers\RetailerMember;
use Sheba\Dal\StrategicPartnerMember\StrategicPartnerMember;

class Profile extends Model {
    protected $guarded  = ['id'];
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
}
