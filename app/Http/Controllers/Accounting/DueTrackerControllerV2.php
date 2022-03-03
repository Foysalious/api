<?php namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Constants\ContactType;

use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Service\DueTrackerService;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Usage\Usage;

class DueTrackerControllerV2 extends Controller
{
    /** @var DueTrackerService */
    protected $dueTrackerService;

    public function __construct(DueTrackerService $dueTrackerService)
    {
        $this->dueTrackerService = $dueTrackerService;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'amount'                => 'required',
            'entry_type'            => 'required|in:due,deposit',
            'account_key'           => 'sometimes|string',
            'customer_id'           => 'required',
            'date'                  => 'required|date_format:Y-m-d H:i:s',
            'partner_wise_order_id' => 'sometimes|numeric',
            'attachments'           => 'sometimes|array',
            'attachments.*'         => 'sometimes|mimes:jpg,jpeg,png,bmp|max:2048'
        ]);
        $response = $this->dueTrackerRepo->setPartner($request->partner)->storeEntry($request, $request->entry_type);
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::DUE_TRACKER_TRANSACTION)->create($request->auth_user);
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getDueListBalance(Request $request)
    {
        $request->contact_type = ContactType::CUSTOMER;
        /*
         * for future development
        $this->validate($request, [
            'contact_type' => 'required|string|in:' . implode(',', ContactType::get())
        ]);
        */
//        $response = $this->dueTrackerService->setPartner($request->partner)
//            ->setContactType($request->contact_type)
//            ->setStartDate($request->start_date)
//            ->setEndDate($request->start_date)
//            ->getDueListBalance();
        $response = [
            "total_transactions" => rand(1,50),
            "total" => rand(1,50),
            "stats" => [
                "deposit"=> rand(20,100),
                "due" => rand(200,1500)
            ]
        ];
        $response['partner'] = [
            'name' => $request->partner->name,
            'avatar' => $request->partner->logo,
            'mobile' => $request->partner->mobile,
        ];

        return http_response($request, null, 200, ['data' => $response]);

    }

    /**
     * @throws AccountingEntryServerError
     */
    public function searchDueList(Request $request)
    {
        $response = $this->dueTrackerService->setPartner($request->partner)
            ->setContactType($request->contact_type)
            ->setOrder($request->order)
            ->setOrderBy($request->order_by)
            ->setBalanceType($request->balance_type)
            ->setLimit($request->limit)
            ->setOffset($request->offset)
            ->setQuery($request->q)
            ->setFilterBySupplier($request->filter_by_supplier)
            ->searchDueList();
        return http_response($request, null, 200, ['data' => $response]);
    }
    public function downloadPdf(Request $request){

        $data=$this->dueTrackerService->downloadPDF($request);
        return api_response($request, null, 200, ['pdf_link' => $data]);

    }
}