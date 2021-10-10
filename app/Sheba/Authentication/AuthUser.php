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
    /** @var array */
    private $payload;

    public function __construct()
    {
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
}
