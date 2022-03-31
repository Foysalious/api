<?php namespace Sheba\Payment\Methods\Bkash;

class BkashCredentialDto
{
  private  $user;
  private  $userType;
  private  $tokenizedId;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return BkashCredentialDto
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @param mixed $userType
     * @return BkashCredentialDto
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTokenizedId()
    {
        return $this->tokenizedId;
    }

    /**
     * @param mixed $tokenizedId
     * @return BkashCredentialDto
     */
    public function setTokenizedId($tokenizedId)
    {
        $this->tokenizedId = $tokenizedId;
        return $this;
    }

}