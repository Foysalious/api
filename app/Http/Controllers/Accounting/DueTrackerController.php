<?php namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepository;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class DueTrackerController extends Controller
{
    use ModificationFields;

    /** @var DueTrackerRepository */
    private $dueTrackerRepo;

    public function __construct(DueTrackerRepository $dueTrackerRepo) {
        $this->dueTrackerRepo = $dueTrackerRepo;
    }

    public function store(Request $request, $customer_id ) {
        $this->validate($request, [
            'amount' => 'required',
            'entry_type' => 'required|in:due,deposit',
            'account_key' => 'required'
        ]);
        $request->merge(['customer_id' => $customer_id]);
        $response = $this->dueTrackerRepo->storeEntry($request, $request->entry_type);
        return api_response($request, $response, 200, ['data' => $response]);
    }

    public function update(Request $request, $customer_id ) {
        $this->validate($request, [
            'amount' => 'required',
            'entry_type' => 'required|in:due,deposit',
            'account_key' => 'required',
            'entry_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d'
        ]);
        $request->merge(['customer_id' => $customer_id]);
        $response = $this->dueTrackerRepo->storeEntry($request, $request->entry_type, true);
        return api_response($request, $response, 200, ['data' => $response]);
    }

}