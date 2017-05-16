<?php

namespace App\Repositories;

use DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class MemberRepository
{
    private $nid_image;

    public function __construct()
    {
        $this->nid_image = 'images/members/nid_image/';
    }

    public function getInfo($member)
    {
        array_forget($member, 'is_verified');
        array_forget($member, 'created_by');
        array_forget($member, 'created_by_name');
        array_forget($member, 'updated_by');
        array_forget($member, 'updated_by_name');
        array_forget($member, 'created_at');
        array_forget($member, 'updated_at');
        array_forget($member, 'remember_token');
        array_add($member, 'name', $member->profile->name);
        array_add($member, 'pro_pic', $member->profile->pro_pic);
        array_forget($member, 'profile');
        return $member;
    }

    public function updateInfo($member, $request)
    {
        try {
            DB::transaction(function () use ($member, $request) {
                $member->father_name = $request->father_name;
                $member->spouse_name = $request->spouse_name;
                $member->mother_name = $request->mother_name;
                $member->nid_no = $request->nid_no;
                $member->alternate_contact = $request->alternate_contact;
                $member->education = $request->education;
                $member->profession = $request->profession;
                $member->references = $request->references;
                $member->bank_account = $request->bank_account;
                $member->mfs_account = $request->mfs_account;
                $member->other_expertise = $request->other_expertise;
                $member->experience = $request->experience;
                $member->present_income = $request->present_income;
                $member->ward_no = $request->ward_no;
                $member->police_station = $request->police_station;
                $member->update();
            });
        } catch (QueryException $e) {
            return false;
        }
        return $member;
    }

    public function updatePersonalInfo($member, $request)
    {
        try {
            DB::transaction(function () use ($member, $request) {
                $member->father_name = $request->father_name;
                $member->spouse_name = $request->spouse_name;
                $member->mother_name = $request->mother_name;
                $member->alternate_contact = $request->alternate_contact;
                $member->bank_account = $request->bank_account;
                $member->mfs_account = $request->mfs_account;
                $member->update();
            });
        } catch (QueryException $e) {
            return false;
        }
        return true;
    }

    public function updateProfessionalInfo($member, $request)
    {
        try {
            DB::transaction(function () use ($member, $request) {
                $member->nid_no = $request->nid_no;
                if ($request->file('nid_image') != null) {
                    if ($member->nid_image != '') {
                        $this->deleteFileFromCDN($member->nid_image);
                    }
                    $member->nid_image = $this->uploadNIDImage($member, $request->file('nid_image'));
                }
                $member->education = $request->education;
                $member->profession = $request->profession;
                $member->other_expertise = $request->other_expertise;
                $member->experience = $request->experience;
                $member->present_income = $request->present_income;
                $member->update();
            });
        } catch (QueryException $e) {
            return false;
        }
        return true;
    }

    private function uploadNIDImage($member, $image)
    {
        $filename = 'member_nid_image' . $member->id . '.' . $image->extension();
        Storage::disk('s3')->put($this->nid_image . $filename, file_get_contents($image), 'public');
        return env('S3_URL') . $this->nid_image . $filename;
    }

    public function deleteFileFromCDN($filename)
    {
        if ($filename != '') {
            Storage::disk('s3')->delete($filename);
        }
    }

}