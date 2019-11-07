<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;

use App\Models\Affiliate;
use App\Repositories\ProfileRepository;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Sheba\Affiliate\Creator;
use Sheba\ExpenseTracker\Repository\EntryRepository;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\Partner\PartnerRepositoryInterface;
use Sheba\Sms\Sms;
use DB;
use Throwable;

class AffiliateRegistrationController extends Controller
{
    use ModificationFields;

    /** @var FacebookAccountKit $fbKit */
    private $fbKit;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var Sms */
    private $sms;
    /** @var EntryRepository $entryRepo */
    private $entryRepo;
    /** @var PartnerRepositoryInterface $partnerRepo */
    private $partnerRepo;

    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->profileRepository = new ProfileRepository();
        $this->sms = new Sms();
    }

    /**
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function registerByProfile(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, []);
            $profile = $request->profile;
            if ($profile->affiliate)
                return api_response($request, null, 409, ['msg' => 'Bondhu already exist']);

            $creator->setProfile($profile)->create();
            return api_response($request, null, 200, ['msg' => 'Bondhu Created Successfully']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
