<?php

namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileCompletionController extends Controller
{
    public function getProfileCompletion($partner, Request $request)
    {
        try {
            $complete_count = 0;
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            if (!empty($partner->name)) $complete_count++;
            if (!empty($partner->basicInformations->address)) $complete_count++;
            if (!empty($partner->logo)) $complete_count++;
            if (count($partner->workingHours) > 0) $complete_count++;
            if (count($partner->locations) > 0) $complete_count++;
            if (count($partner->categories) > 0) $complete_count++;
            if (count($partner->services) > 0) $complete_count++;
            if (count($partner->admins) > 0) $complete_count++;
            if (count($partner->handymanResources) > 0) $complete_count++;
            if (!empty($partner->acc_no) && !empty($partner->acc_name) && !empty($partner->bank_name) && !empty($partner->routing_no)) $complete_count++;
            $resources = $partner->resources->filter(function ($resource) use ($partner) {
                return $resource->pivot->resource_type == 'Handyman' && count($resource->categoriesIn($partner)) > 0;
            });
            if (count($resources) > 0) $complete_count++;
            if (!empty($partner->basicInformations->registration_year)) $complete_count++;
            if (!empty($partner->basicInformations->registration_no)) $complete_count++;
            if (!empty($partner->basicInformations->establishment_year)) $complete_count++;
            if (!empty($partner->basicInformations->trade_license)) $complete_count++;
            $complete = round((($complete_count / 15) * 100), 0);
            return api_response($request, $complete, 200, ['completion' => $complete,
                'personal' => $this->isPersonalInformationGiven($manager_resource),
                'operational' => $this->isOperationalInformationGiven($partner),
                'service' => $this->isServiceInformationGiven($partner),
                'resource' => $this->isResourceInformationGiven($partner),
                'status' => $partner->status,
                'is_verified' => (int) ($partner->status == "Verified"),
                'business_plan' => (int) (!is_null($partner->package_id))
            ]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function isPersonalInformationGiven($resource)
    {
        return !empty($resource->nid_no) && !empty($resource->nid_image) && basename($resource->profile->pro_pic) != 'default.jpg' && !empty($resource->profile->name) ? 1 : 0;
    }

    private function isOperationalInformationGiven($partner)
    {
        return count($partner->workingHours) > 0 && count($partner->locations) > 0 ? 1 : 0;
    }

    private function isResourceInformationGiven($partner)
    {
        return count($partner->admins) > 0 && count($partner->handymanResources) > 0 ? 1 : 0;
    }

    private function isServiceInformationGiven($partner)
    {
        return count($partner->services) > 0 && count($partner->categories) > 0 ? 1 : 0;
    }
}