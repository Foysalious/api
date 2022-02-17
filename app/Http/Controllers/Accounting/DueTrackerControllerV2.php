<?php namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Constants\ContactType;

use App\Sheba\AccountingEntry\Service\DueTrackerService;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class DueTrackerControllerV2 extends Controller
{
    /** @var DueTrackerService */
    protected $dueTrackerService;

    public function __construct(DueTrackerService $dueTrackerService)
    {
        $this->dueTrackerService = $dueTrackerService;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getDueTrackerBalance(Request $request)
    {
        $request->contact_type = ContactType::CUSTOMER;
        /*
         * for future development
        $this->validate($request, [
            'contact_type' => 'required|string|in:' . implode(',', ContactType::get())
        ]);
        */
        $response = $this->dueTrackerService->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->getBalance();
        return http_response($request, null, 200, ['data' => $response]);

    }
}