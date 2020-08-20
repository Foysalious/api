<?php namespace Sheba\OAuth2;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthUser
{
    private $attributes = [];

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @return AuthUser
     * @throws SomethingWrongWithToken
     */
    public static function create()
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) throw new SomethingWrongWithToken("Token is missing.");
            return self::createFromToken($token);
        } catch (JWTException $e) {
            throw new SomethingWrongWithToken($e->getMessage());
        }
    }

    /**
     * @param $token
     * @return AuthUser
     * @throws SomethingWrongWithToken
     */
    public static function createFromToken($token)
    {
        try {
            return new static(JWTAuth::getPayload($token)->toArray());
        } catch (JWTException $e) {
            throw new SomethingWrongWithToken($e->getMessage());
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

    public function isLogisticUser()
    {
        return array_key_exists('logistic_user', $this->attributes);
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
}
