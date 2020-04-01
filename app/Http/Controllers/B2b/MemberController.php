<?php namespace App\Http\Controllers\B2b;

use App\Models\Attachment;
use App\Models\BusinessDepartment;
use App\Models\BusinessRole;
use App\Models\BusinessSmsTemplate;
use App\Models\InspectionItemIssue;
use App\Sheba\Business\ACL\AccessControl;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Sheba\Attachments\FilesAttachment;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Member;
use Carbon\Carbon;
use App\Sheba\Business\LeaveType\Creator as LeaveTypeCreator;
use DB;

class MemberController extends Controller
{
    use ModificationFields;
    use FilesAttachment;

    public function updateBusinessInfo($member, Request $request, LeaveTypeCreator $leave_type_creator)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'no_employee' => 'sometimes|required|integer',
                'lat' => 'sometimes|required|numeric',
                'lng' => 'sometimes|required|numeric',
                'address' => 'required|string',
                'mobile' => 'sometimes|required|string|mobile:bd',
            ]);

            $member = Member::find($member);
            $this->setModifier($member);

            $business_data = [
                'name' => $request->name,
                'employee_size' => $request->no_employee,
                'geo_informations' => json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng]),
                'address' => $request->address,
                'phone' => $request->mobile,
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
                    'is_super' => 1,
                    'join_date' => Carbon::now(),
                ];
                BusinessMember::create($this->withCreateModificationField($member_business_data));
                $this->saveSmsTemplate($business);
                $leave_type_creator->createDefaultLeaveType($member, $business->id);
            }

            return api_response($request, 1, 200, ['business_id' => $business->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function saveSmsTemplate(Business $business)
    {
        $sms_template = new BusinessSmsTemplate();
        $sms_template->business_id = $business->id;
        $sms_template->event_name = "trip_request_accept";
        $sms_template->event_title = "Vehicle Trip Request Accept";
        $sms_template->template = "Your request for vehicle has been accepted. {{vehicle_name}} will be sent to you at {{arrival_time}}";
        $sms_template->variables = "vehicle_name;arrival_time";
        $sms_template->is_published = 1;
        $sms_template->save();
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

    public function getMemberInfo($member, Request $request, AccessControl $access_control)
    {
        try {
            $member = Member::find((int)$member);
            $business = $member->businesses->first();
            $profile = $member->profile;
            $access_control->setBusinessMember($member->businessMember);
            $info = [
                'profile_id' => $profile->id,
                'name' => $profile->name,
                'mobile' => $profile->mobile,
                'email' => $profile->email,
                'pro_pic' => $profile->pro_pic,
                'designation' => ($member->businessMember && $member->businessMember->role) ? $member->businessMember->role->name : null,
                'gender' => $profile->gender,
                'date_of_birth' => $profile->dob ? Carbon::parse($profile->dob)->format('M-j, Y') : null,
                'nid_no' => $profile->nid_no,
                'address' => $profile->address,
                'business_id' => $business ? $business->id : null,
                'remember_token' => $member->remember_token,
                'is_super' => $member->businessMember ? $member->businessMember->is_super : null,
                'access' => [
                    'support' => $business ? (in_array($business->id, config('business.WHITELISTED_BUSINESS_IDS')) && $access_control->hasAccess('support.rw') ? 1 : 0) : 0,
                    'expense' => $business ? (in_array($business->id, config('business.WHITELISTED_BUSINESS_IDS')) && $access_control->hasAccess('expense.rw') ? 1 : 0) : 0,
                    'announcement' => $business ? (in_array($business->id, config('business.WHITELISTED_BUSINESS_IDS')) && $access_control->hasAccess('announcement.rw') ? 1 : 0) : 0
                ]
            ];
            ;
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

    public function storeAttachment($member, Request $request)
    {
        try {
            $this->validate($request, [
                'file' => 'required'
            ]);

            $business_member = $request->business_member;
            $member = $request->member;
            $this->setModifier($member);
            $model = "App\\Models\\" . ucfirst(camel_case($request->type));
            $model = $model::find((int)$request->type_id);
            if (!$request->hasFile('file'))
                return redirect()->back();
            $data = $this->storeAttachmentToCDN($request->file('file'));
            $attachment = $model->attachments()->save(new Attachment($this->withBothModificationFields($data)));
            return api_response($request, $attachment, 200, ['attachment' => $attachment->file]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAttachments($member, Request $request)
    {
        try {
            $business_member = $request->business_member;
            $member = $request->member;
            $model = "App\\Models\\" . ucfirst(camel_case($request->type));
            $model = $model::find((int)$request->type_id);
            if (!$model) return api_response($request, null, 404);
            list($offset, $limit) = calculatePagination($request);
            $attaches = Attachment::where('attachable_type', get_class($model))->where('attachable_id', $model->id)
                ->select('id', 'title', 'file', 'file_type')->orderBy('id', 'DESC')->skip($offset)->limit($limit)->get();
            $attach_lists = [];
            foreach ($attaches as $attach) {
                array_push($attach_lists, [
                    'id' => $attach->id,
                    'title' => $attach->title,
                    'file' => $attach->file,
                    'file_type' => $attach->file_type,
                ]);
            }

            if (count($attach_lists) > 0) return api_response($request, $attach_lists, 200, ['attach_lists' => $attach_lists]);
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
