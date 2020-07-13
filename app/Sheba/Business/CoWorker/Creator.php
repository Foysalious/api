<?php namespace Sheba\Business\CoWorker;

use App\Jobs\SendBusinessRequestEmail;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Business\CoWorker\Requests\BasicRequest;
use Sheba\Helpers\HasErrorCodeAndMessage;

class Creator
{
    use HasErrorCodeAndMessage;
    
    /** @var BasicRequest $basicRequest */
    private $basicRequest;

    /**
     * @param BasicRequest $basic_request
     * @return $this
     */
    public function setBasicRequest(BasicRequest $basic_request)
    {
        $this->basicRequest = $basic_request;
        return $this;
    }

    public function storeBasicInfo()
    {
        return;
    }

    public function create()
    {
        $manager_business_member = null;
        $email_profile = $this->profileRepository->where('email', $request->email)->first();
        $mobile_profile = $this->profileRepository->where('mobile', formatMobile($request->mobile))->first();

        if ($email_profile) $profile = $email_profile;
        elseif ($mobile_profile) $profile = $mobile_profile;
        else $profile = null;

        $co_member = collect();
        /*if ($request->has('manager_employee_id'))
            $manager_business_member = BusinessMember::where([
                ['member_id', $request->manager_employee_id],
                ['business_id', $business->id]
            ])->first();*/

        if (!$profile) {
            $profile = $this->createProfile($member, $request);
            $new_member = $this->makeMember($profile);
            $co_member->push($new_member);

            $business = $member->businesses->first();
            $member_business_data = [
                'business_id' => $business->id,
                'member_id' => $co_member->first()->id,
                'join_date' => Carbon::now(),
                'manager_id' => $manager_business_member ? $manager_business_member->id : null,
                'business_role_id' => $request->role
            ];

            BusinessMember::create($this->withCreateModificationField($member_business_data));
        } else {
            $old_member = $profile->member;
            if ($old_member) {
                if ($old_member->businesses()->where('businesses.id', $business->id)->count() > 0) {
                    return api_response($request, $profile, 200, ['co_worker' => $old_member->id, ['message' => "This person is already added."]]);
                }
                if ($old_member->businesses()->where('businesses.id', '<>', $business->id)->count() > 0) {
                    return api_response($request, null, 403, ['message' => "This person is already connected with another business."]);
                }
                $co_member->push($old_member);
            } else {
                $new_member = $this->makeMember($profile);
                $co_member->push($new_member);
            }
            $this->sendExistingUserMail($profile);
            $member_business_data = [
                'business_id' => $business->id,
                'member_id' => $co_member->first()->id,
                'join_date' => Carbon::now(),
                'manager_id' => $manager_business_member ? $manager_business_member->id : null,
                'business_role_id' => $request->role
            ];

            BusinessMember::create($this->withCreateModificationField($member_business_data));
        }
    }

    /**
     * @TODO NEED TO REMOVE THIS. CREATE FROM PROFILE REPO
     *
     * @param $member
     * @param Request $request
     * @return Profile
     */
    private function createProfile($member, Request $request)
    {
        $this->setModifier($member);
        $password = str_random(6);
        $profile_data = [
            'remember_token' => str_random(255),
            'mobile' => !empty($request->mobile) ? formatMobile($request->mobile) : null,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($password)
        ];
        $profile = Profile::create($this->withCreateModificationField($profile_data));
        dispatch((new SendBusinessRequestEmail($request->email))->setPassword($password)->setTemplate('emails.co-worker-invitation'));

        return $profile;
    }

    private function makeMember($profile)
    {
        $this->setModifier($profile);
        $member = new Member();
        $member->profile_id = $profile->id;
        $member->remember_token = str_random(255);
        $member->save();

        return $member;
    }

    private function sendExistingUserMail($profile)
    {
        $CMail = new SendBusinessRequestEmail($profile->email);
        if (empty($profile->password)) {
            $profile->password = str_random(6);
            $CMail->setPassword($profile->password);
            $profile->save();
        }
        $CMail->setTemplate('emails.co-worker-invitation');
        dispatch($CMail);
    }
}
