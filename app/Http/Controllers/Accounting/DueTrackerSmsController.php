<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Constants\ContactType;
use App\Sheba\AccountingEntry\Service\DueTrackerService;
use App\Sheba\AccountingEntry\Service\DueTrackerSmsService;
use App\Sheba\DueTracker\Exceptions\InsufficientBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\ContactDoesNotExistInDueTracker;
use Sheba\Transactions\Wallet\WalletDebitForbiddenException;

class DueTrackerSmsController extends Controller
{
    protected $dueTrackerSmsService;
    protected $dueTrackerService;

    public function __construct(DueTrackerSmsService $dueTrackerSmsService, DueTrackerService $dueTrackerService)
    {
        $this->dueTrackerSmsService = $dueTrackerSmsService;
        $this->dueTrackerService = $dueTrackerService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     * @throws ContactDoesNotExistInDueTracker
     */
    public function getSmsContent(Request $request): JsonResponse
    {
        $response =  $this->dueTrackerSmsService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->getSmsContentForTagada();

        return http_response($request, null, 200, ['data' => $response]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountingEntryServerError
     * @throws ContactDoesNotExistInDueTracker
     * @throws InsufficientBalance
     * @throws WalletDebitForbiddenException
     */
    public function sendSingleSmsToContact(Request $request): JsonResponse
    {
        $response =  $this->dueTrackerSmsService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->sendSingleSmsToContact();

        return http_response($request, null, 200, ['data' => $response]);
    }

    public function getBulkSmsContactList(Request $request): JsonResponse
    {
        $response =  $this->dueTrackerSmsService
            ->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setContactId($request->contact_id)
            ->setLimit($request->limit ?? 20)
            ->setOffset($request->offset ?? 0)
            ->getBulkSmsContactList();

        return http_response($request, null, 200, ['data' => $response]);
    }

    public function sendBulkSmsToContacts(Request $request): JsonResponse
    {
        $this->validate($request, [
            'contact_type' => 'required|string|in:' . implode(',', ContactType::get()),
            'contact_ids' => 'required|array'
        ]);
        $this->dueTrackerSmsService
            ->setPartner($request->partner)
            ->setContactIds($request->contact_ids)
            ->setContactType($request->contact_type)
            ->sendBulkSmsThroughJob();
        return http_response($request, null, 200, ['data' => true ]);
    }

    public function checkSmsSubscription(Request $request): JsonResponse
    {
        $response = $this->dueTrackerSmsService->setPartner($request->partner)->checkSmsSubscription($request->sending_sms_count);
        return http_response($request, null, 200, ['data' => $response  ]);

    }
}