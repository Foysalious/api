<?php namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Constants\ContactType;
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
        $this->validate($request, [
            'contact_type' => 'required|string|in:' . implode(',', ContactType::get())
        ]);
        $response = $this->dueTrackerService->setPartner($request->partner)->setContactType($request->contact_type)->getBalance($request);
        return http_response($request, null, 200, [ 'data' => $response ]);

    }
}