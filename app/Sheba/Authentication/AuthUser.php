<?php namespace Sheba\Authentication;

use App\Models\BankUser;
use App\Models\Member;
use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Sheba\Authentication\Exceptions\NotEligibleForApplication;
use Sheba\Authentication\Exceptions\ProfileBlacklisted;
use Sheba\Dal\StrategicPartnerMember\StrategicPartnerMember;
use Sheba\Logistics\Exceptions\LogisticServerError;
use Sheba\Logistics\Repository\UserRepository;
use Sheba\Portals\Portals;
use Sheba\Profile\Avatars;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Sync with Api if any update happens
 * Class AuthUser
 * @package Sheba\Authentication
 */
class AuthUser
{
    /** @var Profile */
    private $profile;
    /** @var Resource */
    private $resource;
    /** @var User */
    private $user;
    /** @var Model|null */
    private $avatar;
    /** @var UserRepository */
    private $logisticUsers;
    /** @var array */
    private $payload;
    /** @var string */
    private $portal;

    public function __construct()
    {
        $this->logisticUsers = app()->make(UserRepository::class);
        $this->payload = [];
    }

    /**
     * @param Profile $profile
     * @return $this
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * @param Model $user
     * @return $this
     */
    public function setAvatar(Model $user)
    {
        $this->avatar = $user;
        return $this;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param array $payload
     * @return AuthUser
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        $this->resolveAuthUser();
        return $this;
    }

    /**
     * @param $portal_name
     * @return $this
     */
    public function setPortal($portal_name)
    {
        $this->portal = $portal_name;
        return $this;
    }

    /**
     * @return mixed
     * @throws ProfileBlacklisted
     * @throws NotEligibleForApplication
     */
    public function generateToken()
    {
        $user = $this->getAuthUser();

        if ($user instanceof Profile && $user->isBlacklisted()) throw new ProfileBlacklisted();

        $payload = [
            'name' => $user->name,
            'image' => $user->pro_pic,
            'profile' => $this->generateProfileInfo(),
            'customer' => $this->generateCustomerInfo(),
            'resource' => $this->generateResourceInfo(),
            'member' => $this->generateMemberInfo(),
            'business_member' => $this->generateBusinessMemberInfo(),
            'affiliate' => $this->generateAffiliateInfo(),
            'logistic_user' => $this->generateLogisticUserInfo(),
            'bank_user' => $this->generateBankUserInfo(),
            'strategic_partner_member' => $this->generateStrategicPartnerMemberInfo(),
            'avatar' => $this->generateAvatarInfo()
        ];

        if (!$this->hasAccessToApplication($payload)) throw new NotEligibleForApplication($this->portal);

        return JWTAuth::fromUser($user, $payload);
    }

    public function getAuthUser()
    {
        return $this->profile ? $this->profile : $this->avatar;
    }

    public function resolveAuthUser()
    {
        $this->resolveProfile();
        $this->resolveAvatar();
    }

    public function resolveAvatar()
    {
        if (!$this->payload['avatar']) return;

        $avatar = Avatars::getModelName($this->payload['avatar']['type']);
        $avatar = $avatar::find($this->payload['avatar']['type_id']);
        if ($avatar) $this->setAvatar($avatar);
    }

    public function resolveProfile()
    {
        if (!isset($this->payload['profile'])) return null;
        $profile = Profile::find($this->payload['profile']['id']);
        if ($profile) $this->setProfile($profile);
    }

    /**
     * @return Profile|null
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @return Model|null
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @return Resource|null
     */
    public function getResource()
    {
        if (!$this->profile) return null;
        return $this->profile->resource;
    }

    /**
     * @return Partner|null
     */
    public function getPartner()
    {
        if (!$this->profile || !$this->profile->resource) return null;
        return $this->profile->resource->partners->first();
    }

    private function generateProfileInfo()
    {
        if (!$this->profile) return null;
        return ['id' => $this->profile->id, 'name' => $this->profile->name];
    }

    private function generateCustomerInfo()
    {
        if (!$this->profile || !$this->profile->customer) return null;
        return ['id' => $this->profile->customer->id];
    }

    private function generateResourceInfo()
    {
        $resource = $this->getResource();
        if (!$resource) return null;
        $partner = $this->getPartner();
        if (!$partner) return null;
        return [
            'id' => $resource->id,
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'sub_domain' => $partner->sub_domain,
                'logo' => $partner->logo,
                'is_manager' => $resource->isManager($partner)
            ]
        ];
    }

    private function generateBusinessMemberInfo()
    {
        if (!$this->profile || !$this->profile->member) return null;
        $member = $this->profile->member;
        $business_member = $member ? $member->businessMember : null;

        return [
            'id' => $business_member ? $business_member->id : null,
            'business_id' => $business_member ? $business_member->business_id : null,
            'member_id' => $member->id,
            'is_super' => $business_member ? $business_member->is_super : false
        ];
    }

    private function generateMemberInfo()
    {
        if (!$this->profile || !$this->profile->member) return null;
        return ['id' => $this->profile->member->id];
    }

    private function generateAffiliateInfo()
    {
        if (!$this->profile) return null;
        return $this->profile->affiliate ? ['id' => $this->profile->affiliate->id] : null;
    }

    private function generateLogisticUserInfo()
    {
        try {
            if (!($this->profile instanceof Profile)) return null;
            $logistic_user = $this->logisticUsers->getByProfileId($this->profile->id);
        } catch (LogisticServerError $e) {
            return null;
        }
        if (!$logistic_user) return null;
        $logistic_user = json_decode(json_encode($logistic_user));

        return [
            'id' => $logistic_user->id,
            'is_verified' => $logistic_user->is_verified,
            'company' => [
                'id' => $logistic_user->company->id,
                'name' => $logistic_user->company->name,
                'logo' => $logistic_user->company->logo,
                'sub_domain' => $logistic_user->company->sub_domain,
                'is_rider' => $logistic_user->is_rider,
                'is_manager' => $logistic_user->is_manager,
                'rider_id' => $logistic_user->rider_id
            ]
        ];
    }

    private function generateBankUserInfo()
    {
        if (!$this->profile) return null;
        return $this->profile->bankUser ? ['id' => $this->profile->bankUser->id] : null;
    }

    private function generateAvatarInfo()
    {
        if (!$this->avatar) return null;
        $user = $this->avatar;
        if ($user instanceof Resource) $user = $user->partners()->first();
        elseif ($user instanceof Member) $user = $user->businesses()->first();
        elseif ($user instanceof BankUser) return ['type' => class_basename($user), 'type_id' => $user->id];
        elseif ($user instanceof StrategicPartnerMember) return ['type' => class_basename($user), 'type_id' => $user->id];
        if (!$user) return null;
        return ['type' => strtolower(class_basename($user)), 'type_id' => $user->id];
    }

    /**
     * @param $payload
     * @return boolean
     */
    private function hasAccessToApplication($payload)
    {
        if (is_null($this->portal)) return true;

        if ($this->portal == Portals::EMPLOYEE_APP && is_null($payload['business_member'])) return false;

        return true;
    }

    private function generateStrategicPartnerMemberInfo()
    {
        if (!$this->profile) return null;
        return $this->profile->StrategicPartnerMember()->select('id','role','strategic_partner_id')->first();
    }
}
