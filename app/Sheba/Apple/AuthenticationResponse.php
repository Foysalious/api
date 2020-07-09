<?php namespace Sheba\Apple;


class AuthenticationResponse
{
    private $code;
    private $email;
    private $emailVerified;

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
        return $this->emailVerified;
    }

    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;
        return $this;
    }

    public function hasError()
    {
        return $this->code !== 200;
    }

}