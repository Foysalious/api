<?php namespace App\Http\Controllers\Employee;


use App\Http\Controllers\Controller;
use Sheba\Repositories\ProfileRepository;
use App\Transformers\Business\EmployeeTransformer;
use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class EmployeeController extends Controller
{
    private $repo;

    public function __construct(MemberRepositoryInterface $member_repository)
    {
        $this->repo = $member_repository;
    }

    public function me(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        return api_response($request, null, 200, [
            'info' => (new EmployeeTransformer())->transform($this->repo->find($business_member['member_id']))
        ]);
    }

    public function updateMe(Request $request, ProfileRepository $profile_repo)
    {
        $this->validate($request, [
            'name' => 'string',
            'date_of_birth' => 'date',
            'profile_picture' => 'file',
            'gender' => 'in:Female,Male,Other',
            'address' => 'string',
            'blood_group' => 'in:' . implode(',', getBloodGroupsList(false)),
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $member = $this->repo->find($business_member['member_id']);

        $data = [];
        if($request->has('name')) $data['name'] = $request->name;
        if($request->has('date_of_birth')) $data['dob'] = $request->date_of_birth;
        if($request->hasFile('profile_picture')) {
            $data['pro_pic'] = $profile_repo->saveProPic($request->profile_picture, $data['name'] ?: $member->profile->name);
        }
        if($request->has('gender')) $data['gender'] = $request->gender;
        if($request->has('address')) $data['address'] = $request->address;
        if($request->has('blood_group')) $data['blood_group'] = $request->blood_group;

        $profile_repo->updateRaw($member->profile, $data);

        return api_response($request, null, 200);
    }

    public function updateMyPassword(Request $request, ProfileRepository $profile_repo)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'password' => 'required|confirmed'
        ]);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $this->repo->find($business_member['member_id']);
        $profile = $member->profile;
        if(!password_verify($request->old_password, $profile->password)) {
            return api_response($request, null, 403, [
                'message' => "Old password does not match"
            ]);
        }
        $profile_repo->updatePassword($member->profile, $request->password);
        return api_response($request, null, 200);
    }

    public function getDashboard(Request $request)
    {
        try {
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);
            $member = $this->repo->find($business_member['member_id']);
            if ($business_member) return api_response($request, $business_member, 200, ['info' => [
                'notification_count' => $member->notifications()->unSeen()->count()
            ]]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function getBusinessMember(Request $request)
    {
        $auth_info = $request->auth_info;
        return $auth_info['business_member'];
    }
}
