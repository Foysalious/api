<?php namespace App\Repositories;

use App\Jobs\SendBusinessRequestEmail;

use App\Models\Business;
use App\Models\JoinRequest;
use App\Models\Member;
use App\Models\Profile;

use DB;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Bus\DispatchesJobs;

#use App\Library\Sms;

class BusinessRepository
{
    use DispatchesJobs;
    private $logo_folder;

    public function __construct()
    {
        $this->logo_folder = 'images/companies/';
    }

    public function ifExist($field, $value, $business_id = null)
    {
        $q = Business::where($field, $value);
        if ($business_id != null) {
            $q = $q->where('id', '<>', $business_id);
        }
        return $q->first() == null ? false : true;
    }

    public function create($member, $request)
    {
        $member = Member::find($member);
        $business = new Business();
        try {
            DB::transaction(function () use ($member, $request, $business) {
                $business = $this->addBusinessInfo($business, $request);
                $business->save();
                if ($request->file('logo') != null) {
                    $business->logo = $this->uploadLogo($business, $request->file('logo'));
                    $business->logo_original = $business->logo;
                }
                $business->update();
                $member->businesses()->attach($business, ['type' => 'Admin', 'join_date' => date('Y-m-d')]);
            });
        } catch (QueryException $e) {
            return false;
        }
        return $business;
    }

    public function update($business, $request)
    {
        try {
            DB::transaction(function () use ($business, $request) {
                $business = $this->addBusinessInfo($business, $request);
                $business->update();
            });
        } catch (QueryException $e) {
            return false;
        }
        return true;
    }

    private function addBusinessInfo($business, $request)
    {
        $business->name = $request->name;
        $business->sub_domain = $request->url;
        $business->phone = $request->phone;
        $business->email = $request->email;
        $business->type = $request->type;
        $business->business_category_id = $request->category;
        $business->website = $request->website;
        $business->description = $request->description;
        $business->address = $request->address;
        $business->employee_size = $request->employee_size;
        return $business;
    }

    public function isValidURL($url, $business = null)
    {
        $q = Business::where('sub_domain', $url);
        if ($business != null) {
            $q = $q->where('id', '<>', $business);
        }
        return count($q->first()) == 0 ? true : false;
    }

    public function getBusinesses($member)
    {
        return Member::with(['businesses' => function ($q) {
            $q->select('businesses.id', 'name', 'logo');
        }])->select('id')->where('id', $member)->first();
    }

    public function uploadLogo($business, $logo)
    {
        $filename = 'company_logo_' . $business->id . '.' . $logo->extension();
        $s3 = Storage::disk('s3');
        $s3->put($this->logo_folder . $filename, file_get_contents($logo), 'public');
        return env('S3_URL') . $this->logo_folder . $filename;
    }

    public function sendInvitation($request)
    {
        $joinRequest = new JoinRequest();
        if ($request->profile != '') {
            $profile = Profile::find($request->profile);
            $joinRequest->profile_id = $profile->id;
            $joinRequest->profile_email = $profile->email;
            $joinRequest->profile_mobile = $profile->mobile;
        } elseif ($request->search != '' && filter_var($request->search, FILTER_VALIDATE_EMAIL)) {
            $joinRequest->profile_email = $request->search;
        } else {
            return false;
        }

        $joinRequest->organization_id = $request->business;
        $joinRequest->organization_type = $joinRequest->requester_type = "App\Models\Business";
        $joinRequest->status = 'Pending';
        $joinRequest->save();

        if ($joinRequest->profile_email != '') {
            // config()->set('services.mailgun.domain', config('services.mailgun.business_domain'));
            $this->dispatch(new SendBusinessRequestEmail($joinRequest->profile_email));
            $joinRequest->mail_sent = 1;
            $joinRequest->update();
        }
        return true;
    }

    public function businessExistsForMember($member, $id)
    {
        return $member->businesses()->where('businesses.id', $id)->first();
    }
}
