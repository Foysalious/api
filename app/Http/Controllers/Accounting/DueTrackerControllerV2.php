<?php namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Constants\ContactType;
use App\Sheba\AccountingEntry\Helper\AccountingHelper;
use App\Sheba\AccountingEntry\Service\DueTrackerService;
use Illuminate\Http\Request;

class DueTrackerControllerV2 extends Controller
{
    /** @var DueTrackerService */
    protected $dueTrackerService;

    public function __construct(DueTrackerService $dueTrackerService)
    {
        $this->dueTrackerService = $dueTrackerService;
    }

    public function getDueTrackerBalance(Request $request)
    {
        $request->contact_type = ContactType::CUSTOMER;
        /*
         * for future development
        $this->validate($request, [
            'contact_type' => 'required|string|in:' . implode(',', ContactType::get())
        ]);
        */
        $startDate = AccountingHelper::convertStartDate($request->startDate);
        $endDate = AccountingHelper::convertEndDate($request->endDate);
        if ($endDate < $startDate) {
            return http_response($request, null, 400, ['message' => 'End date can not be smaller than start date']);
        }
        $response = $this->dueTrackerService->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->getBalance();
        return http_response($request, null, 200, ['data' => $response]);

    }
}