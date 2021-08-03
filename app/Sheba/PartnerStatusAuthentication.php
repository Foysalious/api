<?php namespace Sheba;

use App\Http\Middleware\PartnerStatusAuthMiddleware;
use App\Models\Partner;
use Sheba\Authentication\Exceptions\AuthenticationFailedException;

class PartnerStatusAuthentication extends PartnerStatusAuthMiddleware
{
    /**
     * @param Partner $partner
     * @throws AuthenticationFailedException
     */
    public function handleInside(Partner $partner)
    {
        $this->generateException($partner->status);
    }
}