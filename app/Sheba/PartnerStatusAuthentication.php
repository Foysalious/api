<?php namespace Sheba;

use App\Http\Middleware\PartnerStatusAuthMiddleware;
use App\Models\Partner;
use Sheba\Authentication\Exceptions\AuthenticationFailedException;

class PartnerStatusAuthentication extends PartnerStatusAuthMiddleware
{
    /**
     * @param Partner $partner
     * @param string $role
     * @throws AuthenticationFailedException
     */
    public function handleInside(Partner $partner, $role = "both")
    {
        if (in_array($partner->status,$this->access[$role])){
            throw new AuthenticationFailedException("You are not allowed to access this url");
        }
    }
}