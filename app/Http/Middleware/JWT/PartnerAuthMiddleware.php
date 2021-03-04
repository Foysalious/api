<?php namespace App\Http\Middleware\JWT;


class PartnerAuthMiddleware extends JwtAuthMiddleware
{
    protected function hasPassedAuthCheck()
    {
        return $this->authUser->getPartner() ? true : false;
    }
}