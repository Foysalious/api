<?php namespace Sheba\Apple;


class AuthenticationResponse
{
    private $code;
    private $email;
    private $emailVerified;
    private $appleId;
    private $message;

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmailVerified()
    {
        return $this->emailVerified == 'true' ? 1 : 0;
    }

    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;
        return $this;
    }

    public function getAppleId()
    {
        return $this->appleId;
    }

    public function setAppleId($appleId)
    {
        $this->appleId = $appleId;
        return $this;
    }

    public function hasError()
    {
        return $this->code !== 200;
    }

}