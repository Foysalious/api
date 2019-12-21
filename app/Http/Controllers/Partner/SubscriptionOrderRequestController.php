<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequestRepositoryInterface;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequest;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequestRepositoryInterface;
use Sheba\ModificationFields;
use Sheba\SubscriptionOrderRequest\StatusChanger;
use Throwable;
use Illuminate\Validation\ValidationException;

class SubscriptionOrderRequestController extends Controller
{
    use ModificationFields;

    /** @var PartnerOrderRequestRepositoryInterface $orderRequestRepo */
    private $repo;
    /** @var StatusChanger $statusChanger */
    private $statusChanger;

    public function __construct(SubscriptionOrderRequestRepositoryInterface $repo, StatusChanger $status_changer)
    {
        $this->repo = $repo;
        $this->statusChanger = $status_changer;
    }

    /**
     * @param $partner
     * @param SubscriptionOrderRequest $subscription_order_request
     * @param Request $request
     * @return JsonResponse
     */
    public function accept($partner, SubscriptionOrderRequest $subscription_order_request, Request $request)
    {
        try {
            // $this->validate($request, ['resource_id' => 'required|int']);
            $this->statusChanger->setSubscriptionOrderRequest($subscription_order_request)->accept($request);
            if ($this->statusChanger->hasError()) {
                return api_response($request, null, $this->statusChanger->getErrorCode(), [
                    'message' => $this->statusChanger->getErrorMessage()
                ]);
            }
            return api_response($request, null, 200);
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

    /**
     * @param $partner
     * @param SubscriptionOrderRequest $subscription_order_request
     * @param Request $request
     * @return JsonResponse
     */
    public function decline($partner, SubscriptionOrderRequest $subscription_order_request, Request $request)
    {
        try {
            // $this->validate($request, ['resource_id' => 'required|int']);
            $this->statusChanger->setSubscriptionOrderRequest($subscription_order_request)->decline($request);
            if ($this->statusChanger->hasError()) {
                return api_response($request, null, $this->statusChanger->getErrorCode(), [
                    'message' => $this->statusChanger->getErrorMessage()
                ]);
            }
            return api_response($request, null, 200);
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
