<?php namespace App\Http\Controllers\Mtb;


use App\Http\Controllers\Controller;
use App\Sheba\MtbOnboarding\MtbSavePrimaryInformation;
use Illuminate\Http\Request;


class MtbController extends Controller
{
    /**
     * @var MtbSavePrimaryInformation
     */
    private $mtbSavePrimaryInformation;

    public function __construct(MtbSavePrimaryInformation $mtbSavePrimaryInformation)
    {
        $this->mtbSavePrimaryInformation = $mtbSavePrimaryInformation;
    }

    public function apply(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $this->mtbSavePrimaryInformation->setPartner($partner)->storePrimaryInformationToMtb();
        return http_response($request, null, 200, ['message' => 'Successful']);
    }
}
