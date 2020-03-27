<?php namespace Sheba\Authentication;

use App\Models\Member;
use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Model;
use Sheba\Logistics\Exceptions\LogisticServerError;
use Sheba\Logistics\Repository\UserRepository;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthUser
{
    /** @var Profile */
    private $profile;
    /** @var Resource */
    private $resource;
    /** @var Model */
    private $avatar;
    /** @var UserRepository */
    private $logisticUsers;
    /** @var array */
    private $payload;

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

    /**
     * @param array $payload
     * @return AuthUser
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        $this->resolveProfile();
        return $this;
    }

    public function generateToken()
    {
        return JWTAuth::fromUser($this->profile, [
            'name' => $this->profile->name,
            'image' => $this->profile->pro_pic,
            'profile' => $this->generateProfileInfo(),
            'customer' => $this->generateCustomerInfo(),
            'resource' => $this->generateResourceInfo(),
            'member' => $this->generateMemberInfo(),
            'business_member' => $this->generateBusinessMemberInfo(),
            'affiliate' => $this->generateAffiliateInfo(),
            'logistic_user' => $this->generateLogisticUserInfo(),
            'bank_user' => $this->generateBankUserInfo(),
            'avatar' => $this->generateAvatarInfo()
        ]);
    }

    /**
     * @return Profile|null
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
        return $this->profile->resource;
    }

    /**
     * @return Partner|null
     */
    public function getPartner()
    {
        if (!$this->profile->resource) return null;
        return $this->profile->resource->partners->first();
    }

    /**
     * @return Model|null
     */
    public function getAvatar()
    {
        if (!isset($this->payload['avatar'])) return null;
        $model = "App\\Models\\" . ucfirst(camel_case($this->payload['avatar']['type']));
        return $model::find($this->payload['avatar']['type_id']);
    }

    private function generateProfileInfo()
    {
        if (!$this->profile) return null;
        return ['id' => $this->profile->id, 'name' => $this->profile->name];
    }

    private function generateCustomerInfo()
    {
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
        if (!$this->profile->member || !$this->profile->member->businessMember) return null;
        return [
            'id' => $this->profile->member->businessMember->id,
            'business_id' => $this->profile->member->businessMember->business_id,
            'member_id' => $this->profile->member->businessMember->member_id,
        ];
    }

    private function generateMemberInfo()
    {
        return $this->profile->member ? ['id' => $this->profile->member->id] : null;
    }

    private function generateAffiliateInfo()
    {
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
        return $this->profile->bankUser ? ['id' => $this->profile->bankUser->id] : null;
    }

    private function generateAvatarInfo()
    {
        if (!$this->avatar) return null;
        $user = $this->avatar;
        if ($user instanceof Resource) $user = $user->partners()->first();
        elseif ($user instanceof Member) $user = $user->businesses()->first();
        return ['type' => strtolower(class_basename($user)), 'type_id' => $user->id];
    }
}