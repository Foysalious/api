<?php

namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class PersonalInformationController extends Controller
{
    private $fileRepository;

    public function __construct()
    {
        $this->fileRepository = new FileRepository();
    }

    public function index($resource, Request $request)
    {
        try {
            $resource = $request->resource;
            $profile = $resource->profile;
            $info = array(
                'name' => $profile->name,
                'gender' => $profile->gender,
                'birthday' => $profile->dob,
                'address' => $profile->address,
                'picture' => $profile->pro_pic,
                'nid_no' => $resource->nid_no,
                'nid_image' => $resource->nid_image,
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'nid_no' => 'required_without:resource|string',
                'nid_back' => 'required_without:resource|file',
                'nid_front' => 'required_without:resource|file',
                'name' => 'string',
                'gender' => 'string|in:Male,Female,Other',
                'birthday' => 'date_format:Y-m-d|before:' . date('Y-m-d'),
                'address' => 'string',
                'picture' => 'required_without:resource|file',
                'resource' => 'numeric',
                'mobile' => 'required_without:resource|string|mobile:bd'
            ]);
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $by = ["created_by" => $manager_resource->id, "created_by_name" => "Resource - " . $manager_resource->profile->name];
            $pivot_columns = array_merge($by, ['resource_type' => "Handyman"]);
            if ($request->has('resource')) {
                $resource = Resource::find((int)$request->resource);
                $this->createPartnerResource($partner, $resource, $pivot_columns);
            } else {
                $mobile = formatMobile($request->mobile);
                $profile = Profile::where('mobile', $mobile)->first();
                if (!$profile) {
                    $profile = Profile::create(array_merge($by, ['mobile' => $mobile]));
                }
                $resource = Resource::create(array_merge($by, ['remember_token' => str_random(255), 'profile_id' => $profile->id]));
                $resource = $this->updateInformation($request, $profile, $resource);
                $this->createPartnerResource($partner, $resource, $pivot_columns);
            }
            return api_response($request, $resource, 200);
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

    private function createPartnerResource(Partner $partner, Resource $resource, $pivot_columns)
    {
        $partner->resources()->attach($resource->id, $pivot_columns);
    }

    public function update($resource, Request $request)
    {
        try {
            $this->validate($request, [
                'nid_no' => 'string',
                'nid_back' => 'file',
                'nid_front' => 'file',
                'name' => 'string',
                'gender' => 'string|in:Male,Female,Other',
                'birthday' => 'date_format:Y-m-d|before:' . date('Y-m-d'),
                'address' => 'string',
                'picture' => 'file',
            ]);
            $resource = $request->resource;
            $profile = $resource->profile;
            $resource = $this->updateInformation($request, $profile, $resource);
            return api_response($request, $resource, 200);
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

    private function mergeFrontAndBackNID($profile, $front, $back)
    {
        $img1 = Image::make($front);
        $img2 = Image::make($back);
        $canvas = Image::canvas($img1->width() + $img2->width(), max($img1->height(), $img2->height()));
        $canvas->insert($img1, 'top-left');
        $canvas->insert($img2, 'top-right');
        $canvas->encode('png');
        return $this->fileRepository->uploadImageToCDN('images/resources/nid', Carbon::now()->timestamp . '_' . str_slug($profile->name, '_') . '.png', $canvas);
    }

    private function makeProfilePicName($profile, $photo)
    {
        return $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id . '.' . $photo->extension();
    }

    private function updateInformation(Request $request, $profile, $resource)
    {
        if ($request->hasFile('picture')) {
            $picture = $request->file('picture');
            $profile->pro_pic = $this->fileRepository->uploadToCDN($this->makeProfilePicName($profile, $picture), $picture, 'images/profiles/');
        }
        $profile->update(array_merge($request->only(['name', 'gender', 'address']), ['dob' => $request->birthday]));
        $nid_image_link = $this->mergeFrontAndBackNID($profile, $request->file('nid_front'), $request->file('nid_back'));
        $resource->update(['nid_no' => $request->nid_no, 'nid_image' => $nid_image_link]);
        return $resource;
    }

}