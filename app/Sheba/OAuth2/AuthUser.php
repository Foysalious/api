<?php namespace Sheba\OAuth2;

use App\Models\Affiliate;
use App\Models\Business;
use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Namshi\JOSE\JWS;
use Sheba\Profile\Avatars;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class AuthUser
{
    private $attributes = [];
    /** @var Profile */
    private $profile;
    /** @var Resource */
    private $resource;
    /** @var User */
    private $user;
    /** @var Model|null */
    private $avatar;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
        $this->resolveAuthUser();
    }

    /**
     * @return AuthUser
     * @throws SomethingWrongWithToken
     */
    public static function create()
    {
        try {
            $token = self::getToken();
            if (!$token) throw new SomethingWrongWithToken("Token is missing.");
            return self::createFromToken($token);
        } catch (JWTException $e) {
            throw new SomethingWrongWithToken($e->getMessage());
        }
    }

    /**
     * @return Token
     * @throws SomethingWrongWithToken
     */
    public static function getToken()
    {
        try {
            return JWTAuth::getToken();
        } catch (JWTException $e) {
            throw new SomethingWrongWithToken($e->getMessage());
        }
    }

    public static function authenticate()
    {
        JWTAuth::getPayload(JWTAuth::getToken());
    }

    /**
     * @param $token
     * @return AuthUser
     * @throws SomethingWrongWithToken
     */
    public static function createFromToken($token)
    {
        try {
            if (request()->url() == config('sheba.api_url') . '/v2/top-up/get-topup-token') {
                $jws = JWS::load($token);
                $payload = $jws->getPayload();
            } else {
                $payload = JWTAuth::getPayload($token)->toArray();
            }
            return new static($payload);
        } catch (JWTException $e) {
            throw new SomethingWrongWithToken($e->getMessage(), $e->getStatusCode());
        }
    }

    public function getName()
    {
        return $this->attributes['name'];
    }

    public function isEmailVerified()
    {
        return $this->attributes['profile']['email_verified'];
    }

    public function getProfileId()
    {
        return $this->attributes['profile']['id'];
    }

    public function isLogisticUser()
    {
        return array_key_exists('logistic_user', $this->attributes);
    }

    public function isCustomer()
    {
        return !is_null($this->attributes['customer']);
    }

    public function getCustomerId()
    {
        if (!$this->isCustomer()) return null;
        return $this->attributes['customer']['id'];
    }

    public function isMember()
    {
        return !is_null($this->attributes['member']);
    }

    public function doesMemberHasBusiness()
    {
        if (!$this->isMember()) return false;
        return !is_null($this->attributes['business_member']['business_id']);
    }

    public function getMemberId()
    {
        if (!$this->isMember()) return null;
        return $this->attributes['business_member']['member_id'];
    }

    public function getMemberAssociatedBusinessId()
    {
        if (!$this->doesMemberHasBusiness()) return null;
        return $this->attributes['business_member']['business_id'];
    }

    public function isMemberSuper()
    {
        if (!$this->doesMemberHasBusiness()) return null;
        return $this->attributes['business_member']['is_super'];
    }

    public function toArray()
    {
        return $this->attributes;
    }

    public function toJson()
    {
        return json_encode($this->attributes);
    }

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
     * @param $portal_name
     * @return AuthUser
     *
     *
     * /**
     * @return $this
     */
    public function setPortal($portal_name)
    {
        $this->portal = $portal_name;
        return $this;
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
        if (!$this->attributes['avatar']) return;
        $avatar = Avatars::getModelName($this->attributes['avatar']['type']);
        $avatar = $avatar::find($this->attributes['avatar']['type_id']);
        if ($avatar) $this->setAvatar($avatar);
    }

    public function resolveProfile()
    {
        if (!isset($this->attributes['profile'])) return null;
        $profile = Profile::find($this->attributes['profile']['id']);
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
     * @return Affiliate|null
     */
    public function getAffiliate()
    {
        if (!$this->profile) return null;
        return $this->profile->affiliate;
    }

    /**
     * @return Partner|null
     */
    public function getPartner()
    {
        if (!$this->profile || !$this->profile->resource) return null;
        return $this->profile->resource->partners->first();
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return Business|null
     */
    public function getBusiness()
    {
        if (!$this->profile || !$this->profile->member) return null;
        $member = $this->profile->member;
        $business_member = $member ? $member->businessMember : null;
        if (!$business_member) return null;
        return $business_member->business;
    }
}
