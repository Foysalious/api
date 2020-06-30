<?php namespace Sheba\Authentication;

use App\Models\BankUser;
use App\Models\Member;
use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Sheba\Logistics\Exceptions\LogisticServerError;
use Sheba\Logistics\Repository\UserRepository;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Sync with Api if any update happens
 * Class AuthUser
 *
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
    /** @var Model */
    private $avatar;
    /** @var UserRepository */
    private $logisticUsers;
    /** @var array */
    private $payload;

    public function __construct()
    {
        $this->logisticUsers = app()->make(UserRepository::class);
        $this->payload       = [];
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

    public function generateToken()
    {
        return JWTAuth::fromUser($this->getAuthUser(), [
            'name'            => $this->getAuthUser()->name,
            'image'           => $this->getAuthUser()->pro_pic,
            'profile'         => $this->generateProfileInfo(),
            'customer'        => $this->generateCustomerInfo(),
            'resource'        => $this->generateResourceInfo(),
            'member'          => $this->generateMemberInfo(),
            'business_member' => $this->generateBusinessMemberInfo(),
            'affiliate'       => $this->generateAffiliateInfo(),
            'logistic_user'   => $this->generateLogisticUserInfo(),
            'bank_user'       => $this->generateBankUserInfo(),
            'avatar'          => $this->generateAvatarInfo()
        ]);
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

    /**
     * @return null
     */
    public function resolveAvatar()
    {
        if ($this->profile) return null;
        $avatar = $this->getAvatar();
        if ($avatar) $this->setAvatar($avatar);
    }

    /**
     * @return null
     */
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
        if (!$this->profile) return null;
        if (!$this->profile->resource) return null;
        return $this->profile->resource->partners->first();
    }

    /**
     * @return Model|null
     */
    public function getAvatar()
    {
        $model = $this->payload['avatar']['type'] !== 'strategicpartnermember' ? "App\\Models\\" . ucfirst(camel_case($this->payload['avatar']['type'])) : "Sheba\\Dal\\StrategicPartnerMember\\StrategicPartnerMember";
        return $model::find($this->payload['avatar']['type_id']);
    }

    private function generateProfileInfo()
    {
        if (!$this->profile) return null;
        return ['id' => $this->profile->id, 'name' => $this->profile->name];
    }

    private function generateCustomerInfo()
    {
        if (!$this->profile) return null;
        if (!$this->profile->customer) return null;
        return ['id' => $this->profile->customer->id];
    }

    private function generateResourceInfo()
    {
        $resource = $this->getResource();
        if (!$resource) return null;
        $partner = $this->getPartner();
        if (!$partner) return null;
        return [
            'id'      => $resource->id,
            'partner' => [
                'id'         => $partner->id,
                'name'       => $partner->name,
                'sub_domain' => $partner->sub_domain,
                'logo'       => $partner->logo,
                'is_manager' => $resource->isManager($partner)
            ]
        ];
    }

    private function generateBusinessMemberInfo()
    {
        if (!$this->profile) return null;
        if (!$this->profile->member || !$this->profile->member->businessMember) return null;
        return [
            'id'          => $this->profile->member->businessMember->id,
            'business_id' => $this->profile->member->businessMember->business_id,
            'member_id'   => $this->profile->member->businessMember->member_id,
        ];
    }

    private function generateMemberInfo()
    {
        if (!$this->profile) return null;
        return $this->profile->member ? ['id' => $this->profile->member->id] : null;
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
            'id'          => $logistic_user->id,
            'is_verified' => $logistic_user->is_verified,
            'company'     => [
                'id'         => $logistic_user->company->id,
                'name'       => $logistic_user->company->name,
                'logo'       => $logistic_user->company->logo,
                'sub_domain' => $logistic_user->company->sub_domain,
                'is_rider'   => $logistic_user->is_rider,
                'is_manager' => $logistic_user->is_manager,
                'rider_id'   => $logistic_user->rider_id
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
        if (!$user) return null;
        return ['type' => strtolower(class_basename($user)), 'type_id' => $user->id];
    }
}
