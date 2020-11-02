<?php namespace App\Http\Controllers\B2b;

use App\Models\Member;
use Sheba\Business\Prorate\Updater;
use Sheba\Dal\BusinessMemberLeaveType\Contract as BusinessMemberLeaveTypeInterface;
use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;
use App\Http\Controllers\Controller;
use Sheba\Business\Prorate\Creator;
use Sheba\Business\Prorate\Requester as ProrateRequester;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class ProrateController extends Controller
{
    use ModificationFields;

    /** @var BusinessMemberLeaveTypeInterface $businessMemberLeaveTypeRepo */
    private $businessMemberLeaveTypeRepo;
    /** @var ProrateRequester $requester */
    private $requester;
    /** @var Creator $creator */
    private $creator;
    /**@var Updater $updater */
    private $updater;

    /**
     * ProrateController constructor.
     * @param ProrateRequester $prorate_requester
     * @param Creator $creator
     * @param Updater $updater
     * @param BusinessMemberLeaveTypeInterface $business_member_leave_type_repo
     */
    public function __construct(ProrateRequester $prorate_requester, Creator $creator, Updater $updater, BusinessMemberLeaveTypeInterface $business_member_leave_type_repo)
    {
        $this->requester = $prorate_requester;
        $this->creator = $creator;
        $this->updater = $updater;
        $this->businessMemberLeaveTypeRepo = $business_member_leave_type_repo;
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

    public function edit($business, $prorate, Request $request)
    {
        /**@var BusinessMemberLeaveType $business_member_leave_type */
        $business_member_leave_type = $this->businessMemberLeaveTypeRepo->find($prorate);
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        $this->requester->setLeaveTypeId($request->leave_type_id)
            ->setTotalDays($request->total_days)
            ->setNote($request->note);

        $this->updater->setRequester($this->requester)->setBusinessMemberLeaveType($business_member_leave_type)->update();
        return api_response($request, null, 200);
    }

    public function delete($business, Request $request)
    {
        $this->validate($request, [
            'business_member_leave_type_ids' => 'required|array',
        ]);
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        foreach ($request->business_member_leave_type_ids as $id){
            /**@var BusinessMemberLeaveType $business_member_leave_type */
            $business_member_leave_type = $this->businessMemberLeaveTypeRepo->find($id);
            $this->businessMemberLeaveTypeRepo->delete($business_member_leave_type);
        }
        return api_response($request, null, 200);
    }
}