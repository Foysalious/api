<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Partner;
use App\Sheba\Business\Bid\Updater;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BidRepositoryInterface;
use Sheba\ShebaAccountKit\Client;
use Sheba\ShebaAccountKit\Requests\ApiTokenRequest;
use Sheba\ShebaAccountKit\Requests\OtpSendRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;

class ProposalController extends Controller
{
    use ModificationFields;

    /** @var BidRepositoryInterface $bid_repo */
    private $bidRepo;

    public function __construct(BidRepositoryInterface $bid_repo)
    {
        $this->bidRepo = $bid_repo;
    }

    /**
     * @param $tender
     * @param $proposal
     * @param Request $request
     * @param ShebaAccountKit $sheba_account_kit
     * @param ApiTokenRequest $api_token_request
     * @param Client $client
     * @param OtpSendRequest $sms_send_request
     * @return JsonResponse
     */
    public function sendPin($tender, $proposal, Request $request, ShebaAccountKit $sheba_account_kit, ApiTokenRequest $api_token_request, Client $client, OtpSendRequest $sms_send_request)
    {
        /** @var Bid $proposal */
        $proposal = $this->bidRepo->find($proposal);
        if (!$proposal) return api_response($request, null, 404);
        if ($proposal->procurement_id != (int)$tender) return api_response($request, null, 404);

        $app_id = config('sheba_accountkit.app_id');
        $api_token_request->setAppId($app_id);
        $token = $sheba_account_kit->getToken($api_token_request);
        /** @var Partner $partner */
        $partner = $proposal->bidder;
        $mobile = $partner->getManagerMobile();
        $sms_send_request->setAppId($app_id)->setApiToken($token)->setMobile($mobile);
        $client->sendOtp($sms_send_request);

        return api_response($request, null, 200, ['data' => ['mobile' => $mobile, 'token' => $token]]);
    }
}

