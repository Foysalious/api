<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
use App\Models\Member;
use App\Sheba\Business\BusinessBasicInformation;
use App\Http\Controllers\Controller;
use Sheba\Business\Prorate\Creator;
use Sheba\Business\Prorate\Requester as ProrateRequester;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class ProrateController extends Controller
{
    use ModificationFields, BusinessBasicInformation;

    /** @var ProrateRequester $requester */
    private $requester;
    /** @var Creator $creator */
    private $creator;

    /**
     * ProrateController constructor.
     * @param ProrateRequester $prorate_requester
     * @param Creator $creator
     */
    public function __construct(ProrateRequester $prorate_requester, Creator $creator)
    {
        $this->requester = $prorate_requester;
        $this->creator = $creator;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'business_member_ids' => 'required|array',
            'leave_type_id' => 'required',
            'total_days' => 'required'
        ]);

        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        $this->requester->setBusinessMemberIds($request->business_member_ids)
            ->setTotalDays($request->total_days)
            ->setLeaveTypeId($request->leave_type_id)
            ->setNote($request->note);

        $this->creator->setRequester($this->requester)->create();
        return api_response($request, null, 200);
    }

}