<?php

namespace App\Repositories\Business;

use App\Models\Business;
use App\Models\Member;
use DB;
use Illuminate\Support\Facades\Storage;

class BusinessRepository
{
    private $logo_folder;

    public function __construct()
    {
        $this->logo_folder = 'images/companies/';
    }

    public function create($member, $request)
    {
        $member = Member::find($member);
        try {
            DB::transaction(function () use ($member, $request) {
                $business = new Business();
                $business = $this->addBusinessInfo($business, $request);
                $business->save();
                $business->logo = $this->uploadLogo($business, $request->file('logo'));
                $business->logo_original = $business->logo;
                $business->update();
                $member->businesses()->attach($business);
            });
        } catch (QueryException $e) {
            return false;
        }
        return true;
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
            $q->select('name', 'logo');
        }])->select('id')->where('id', $member)->first();
    }

    public function uploadLogo($business, $logo)
    {
        $filename = 'company_logo_' . $business->id . '.' . $logo->extension();
        $s3 = Storage::disk('s3');
        $s3->put($this->logo_folder . $filename, file_get_contents($logo), 'public');
        return env('S3_URL') . $this->logo_folder . $filename;
    }
}