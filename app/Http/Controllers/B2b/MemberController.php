<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessDepartment;
use App\Models\BusinessRole;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Member;
use Carbon\Carbon;
use DB;

class MemberController extends Controller
{
    use ModificationFields;

    public function updateBusinessInfo($member, Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'no_employee' => 'required|integer',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'address' => 'required|string',
            ]);
            $member = Member::find($member);
            $this->setModifier($member);

            $business_data = [
                'name' => $request->name,
                'employee_size' => $request->no_employee,
                'geo_informations' => json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng]),
                'address' => $request->address,
            ];
            if (count($member->businesses) > 0) {
                $business = $member->businesses->first();
                $business->update($this->withUpdateModificationField($business_data));
            } else {
                $business_data['sub_domain'] = $this->guessSubDomain($request->name);
                $business = Business::create($this->withCreateModificationField($business_data));
                $this->tagDepartment($business);
                $this->tagRole($business);
                $member_business_data = [
                    'business_id' => $business->id,
                    'member_id' => $member->id,
                    'type' => 'Admin',
                    'join_date' => Carbon::now(),
                ];
                BusinessMember::create($this->withCreateModificationField($member_business_data));
            }

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getBusinessInfo($member, Request $request)
    {
        try {
            $member = Member::find((int)$member);
            $profile = $member->profile;

            if ($business = $member->businesses->first()) {
                $info = [
                    "name" => $business->name,
                    "sub_domain" => $business->sub_domain,
                    "tagline" => $business->tagline,
                    "company_type" => $business->type,
                    "address" => $business->address,
                    "geo_informations" => json_decode($business->geo_informations),
                    "wallet" => (double)$business->wallet,
                    "employee_size" => $business->employee_size,
                ];
                return api_response($request, $info, 200, ['info' => $info]);
            } else {
                return api_response($request, null, 404, ["message" => 'Business not found.']);
            }

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getMemberInfo($member, Request $request)
    {
        try {
            $member = Member::find((int)$member);
            $business = $member->businesses->first();
            $profile = $member->profile;
            $info = [
                'profile_id' => $profile->id,
                'name' => $profile->name,
                'mobile' => $profile->mobile,
                'email' => $profile->email,
                'pro_pic' => $profile->pro_pic,
                'designation' => count($member->businessMember) > 0 ? $member->businessMember->first()->type : null,
                'gender' => $profile->gender,
                'date_of_birth' => $profile->dob ? Carbon::parse($profile->dob)->format('M-j, Y') : null,
                'nid_no' => $profile->nid_no,
                'address' => $profile->address,
                'business_id' => $business ? $business->id : null,
            ];
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function guessSubDomain($name)
    {
        $blacklist = ["google", "facebook", "microsoft", "sheba", "sheba.xyz"];
        $base_name = $name = preg_replace('/-$/', '', substr(strtolower(clean($name)), 0, 15));
        $already_used = Business::select('sub_domain')->lists('sub_domain')->toArray();
        $counter = 0;
        while (in_array($name, array_merge($blacklist, $already_used))) {
            $name = $base_name . $counter;
            $counter++;
        }
        return $name;
    }

    public function index(Request $request)
    {
        try {
            $list = [];
            $request->business->load('members.profile');
            $members = $request->business->members;
            foreach ($request->business->members as $member) {
                array_push($list, [
                        'id' => $member->id,
                        'name' => $member->profile->name
                    ]
                );
            }
            if (count($members) > 0) return api_response($request, $members, 200, ['members' => $list]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function tagDepartment(Business $business)
    {
        $departments = ['IT', 'FINANCE', 'HR', 'ADMIN', 'MARKETING', 'OPERATION', 'CXO'];
        foreach ($departments as $department) {
            $dept = new BusinessDepartment();
            $dept->name = $department;
            $dept->business_id = $business->id;
            $dept->save();
        }
    }


    private function tagRole(Business $business)
    {
        $roles = ['Manager', 'VP', 'Executive', 'Intern', 'Senior Executive', 'Driver'];
        $depts = BusinessDepartment::where('business_id', $business->id)->pluck('id')->toArray();
        foreach ($roles as $role) {
            foreach ($depts as $dept) {
                $b_role = new BusinessRole();
                $b_role->name = $role;
                $b_role->business_department_id = $dept;
                $b_role->save();
            }
        }
    }

}
