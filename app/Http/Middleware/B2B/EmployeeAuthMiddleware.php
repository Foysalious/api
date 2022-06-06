<?php namespace App\Http\Middleware\B2B;

use App\Exceptions\NotFoundException;
use App\Http\Middleware\JWTAuthentication;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\BusinessBasicInformation;

class EmployeeAuthMiddleware extends JWTAuthentication
{
    use BusinessBasicInformation;

    protected function setExtraDataToRequest($request)
    {
        parent::setExtraDataToRequest($request);

        /** @var Business $business */
        $business = $this->getBusiness($request);
        if (!$business) throw new NotFoundException('Business not found.', 404);
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) throw new NotFoundException('Business Member not found.', 404);
        $member = $this->getMember($request);
        $request->merge(['business' => $business, 'business_member' => $business_member, 'member' => $member]);
    }
}
