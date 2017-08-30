<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Repositories\FileRepository;
use App\Repositories\LocationRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Validator;
use DB;

class AffiliateController extends Controller
{
    private $fileRepository;
    private $locationRepository;

    public function __construct()
    {
        $this->fileRepository = new FileRepository();
        $this->locationRepository = new LocationRepository();
    }

    public function edit($affiliate, Request $request)
    {
        $except_fields = [];
        if ($request->has('bkash_no')) {
            $mobile = formatMobile(ltrim($request->bkash_no));
            $request->merge(['bkash_no' => $mobile]);
        } else {
            $except_fields = ['bkash_no'];
        }
        $validation_fields = count($except_fields) > 0 ? $request->except($except_fields) : $request->all();
        if ($msg = $this->_validateEdit($validation_fields)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $affiliate = Affiliate::find($affiliate);
        if ($request->has('name')) {
            $profile = $affiliate->profile;
            $profile->name = $request->name;
            $profile->update();
        }
        if ($request->has('bkash_no')) {
            $banking_info = $affiliate->banking_info;
            $banking_info->bKash = $mobile;
            $affiliate->banking_info = json_encode($banking_info);
        }
        if ($request->has('geolocation')) {
            //                $location = json_decode($request->geolocation);
//                $this->locationRepository->getLocationFromLatLng($location->lat . ',' . $location->lng);
            $affiliate->geolocation = $request->geolocation;
        }
        return $affiliate->update() ? response()->json(['code' => 200]) : response()->json(['code' => 404]);
    }

    public function getStatus($affiliate, Request $request)
    {
        $affiliate = Affiliate::where('id', $affiliate)->select('verification_status', 'is_suspended')->first();
        return $affiliate != null ? response()->json(['code' => 200, 'affiliate' => $affiliate]) : response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    public function updateProfilePic(Request $request)
    {
        if ($msg = $this->_validateImage($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $photo = $request->file('photo');
        $profile = Affiliate::find($request->affiliate)->profile;
        if (strpos($profile->pro_pic, 'images/customer/avatar/default.jpg') == false) {
            $filename = substr($profile->pro_pic, strlen(env('S3_URL')));
            $this->fileRepository->deleteFileFromCDN($filename);
        }
        $filename = $profile->id . '_profile_image_' . Carbon::now()->timestamp . '.' . $photo->extension();
        $profile->pro_pic = $this->fileRepository->uploadToCDN($filename, $request->file('photo'), 'images/profiles/');
        return $profile->update() ? response()->json(['code' => 200, 'picture' => $profile->pro_pic]) : response()->json(['code' => 404]);
    }

    public function getWallet($affiliate, Request $request)
    {
        $affiliate = Affiliate::find($affiliate);
        return $affiliate != null ? response()->json(['code' => 200, 'wallet' => $affiliate->wallet]) : response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    public function leadInfo($affiliate, Request $request)
    {
        $affiliate = Affiliate::find($affiliate);
        return response()->json(['code' => 200, 'total_lead' => $affiliate->totalLead(), 'earning_amount' => $affiliate->earningAmount()]);
    }

    public function joinClan($affiliate, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string'
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->all()[0];
                return api_response($request, $error, 400, ['msg' => $error]);
            }
            $affiliate = $request->affiliate;
            if ($affiliate->is_ambassador == 1 || $affiliate->ambassador_id != null) {
                return api_response($request, null, 403);
            }
            $ambassador = Affiliate::where([
                ['ambassador_code', 'like', '%' . $request->code . '%'],
                ['id', '<>', $affiliate->id],
                ['is_ambassador', 1]
            ])->first();
            if ($ambassador) {
                $affiliate = $request->affiliate;
                $affiliate->ambassador_id = $ambassador->id;
                $affiliate->update();
                return api_response($request, true, 200);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function getLeaderboard($affiliate, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $affiliates = Affiliate::whereHas('transactions', function ($q) {
                $q->where('type', 'Credit');
            })->select('id', 'profile_id')->get();

            $affiliates->load(['profile' => function ($q) {
                $q->select('id', 'name', 'pro_pic');
            }])->load(['affiliations' => function ($q) {
                $q->where('status', 'successful');
            }])->load('transactions');

            foreach ($affiliates as $affiliate) {
                $affiliate['earning_amount'] = $affiliate->transactions->sum('amount');
                $affiliate['total_reference'] = $affiliate->affiliations->count();
                $affiliate['name'] = $affiliate->profile->name;
                $affiliate['picture'] = $affiliate->profile->pro_pic;
                array_forget($affiliate, 'transactions');
                array_forget($affiliate, 'affiliations');
                array_forget($affiliate, 'profile');
                array_forget($affiliate, 'profile_id');
            }
            $affiliates = $affiliates->sortByDesc('earning_amount')->splice($offset, $limit)->values();
            return api_response($request, $affiliates, 200, ['affiliates' => $affiliates]);
        } catch (Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function getAgents($affiliate, Request $request)
    {
        try {
            $affiliate = $request->affiliate;
            if ($affiliate->is_ambassador == 0) {
                return api_response($request, null, 403);
            }
            $affiliate->load(['agents' => function ($q) {
                $q->select('id', 'profile_id', 'ambassador_id', 'total_gifted_number', 'total_gifted_amount')->with(['profile' => function ($q) {
                    $q->select('id', 'name', 'pro_pic');
                }]);
            }]);
            if (count($affiliate->agents) != 0) {
                foreach ($affiliate->agents as $agent) {
                    $agent['name'] = $agent->profile->name;
                    $agent['picture'] = $agent->profile->pro_pic;
                    $agent['total_gifted_amount'] = (double)$agent->total_gifted_amount;
                    array_forget($agent, 'profile');
                    array_forget($agent, 'ambassador_id');
                    array_forget($agent, 'profile_id');
                }
                list($offset, $limit) = calculatePagination($request);
                $agents = $affiliate->agents->splice($offset, $limit)->values();
                return api_response($request, $agents, 200, ['agents' => $agents]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (Exception $e) {
            return api_response($request, null, 500);
        }
    }

    private function _validateImage($request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|mimes:jpeg,png|max:500'
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    private function _validateEdit($request)
    {
        $validator = Validator::make($request, [
            'bkash_no' => 'sometimes|required|string|mobile:bd',
        ], ['mobile' => 'Invalid bKash number!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }


}
