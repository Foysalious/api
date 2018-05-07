<?php

namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use DB;

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
            ], ['mobile' => 'Invalid mobile number!']);
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $by = ["created_by" => $manager_resource->id, "created_by_name" => "Resource - " . $manager_resource->profile->name];
            $pivot_columns = array_merge($by, ['resource_type' => "Handyman"]);
            if ($request->has('resource')) {
                if ($partner->hasThisResource((int)$request->resource, 'Handyman')) {
                    return api_response($request, null, 403, ['message' => 'The resource is already added with you!']);
                }
                $resource = Resource::find((int)$request->resource);
                if ($this->createPartnerResource($partner, $resource, $pivot_columns))
                    return api_response($request, $resource, 200);
                else
                    return api_response($request, null, 500);
            } else {
                try {
                    $resource = '';
                    DB::transaction(function () use ($request, $by, $partner, $pivot_columns, $resource) {
                        $mobile = formatMobile($request->mobile);
                        $profile = Profile::where('mobile', $mobile)->first();
                        if (!$profile) $profile = Profile::create(array_merge($by, ['mobile' => $mobile]));
                        $resource = $profile->resource;
                        if ($resource) return api_response($request, null, 403, ['message' => 'There is already a resource exists at this number!']);
                        $resource = Resource::create(array_merge($by, ['remember_token' => str_random(255), 'profile_id' => $profile->id]));
                        $resource = $this->updateInformation($request, $profile, $resource);
                        $this->createPartnerResource($partner, $resource, $pivot_columns);
                    });
                    return api_response($request, $resource, 200);
                } catch (QueryException $e) {
                    app('sentry')->captureException($e);
                    return api_response($request, null, 500);
                }
            }
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
        try {
            DB::transaction(function () use ($partner, $resource, $pivot_columns) {
                $partner->resources()->attach($resource->id, $pivot_columns);
                $partner_resources = PartnerResource::whereIn('id', $partner->handymanResources->pluck('pivot.id')->toArray())->get();
                $category_ids = $partner->categories->pluck('id')->toArray();
                $partner_resources->each(function ($partner_resource) use ($category_ids) {
                    $partner_resource->categories()->sync($category_ids);
                });
            });
            return true;
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return null;
        }
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
                'mobile' => 'string|mobile:bd'
            ], ['mobile' => 'Invalid mobile number!']);
            $resource = $request->resource;
            $profile = $resource->profile;
            if ($request->has('mobile')) {
                $mobile = formatMobile($request->mobile);
                if ($profile->mobile != $mobile) {
                    $mobile_profile = Profile::where('mobile', $mobile)->first();
                    if ($mobile_profile) {
                        return api_response($request, null, 403, ['message' => 'There is already a resource exists at this number!']);
                    } else {
                        $request->merge(['mobile' => $mobile]);
                    }
                } else {
                    array_forget($request, 'mobile');
                }
            }
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

    private function updateInformation(Request $request, Profile $profile, $resource)
    {
        if ($request->hasFile('picture')) {
            $picture = $request->file('picture');
            $profile->pro_pic = $this->fileRepository->uploadToCDN($this->makeProfilePicName($profile, $picture), $picture, 'images/profiles/');
        }
        if ($request->has('mobile')) $profile->mobile = formatMobile($request->mobile);
        if ($request->has('name')) $profile->name = $request->name;
        if ($request->has('address')) $profile->address = $request->address;
        $profile->update();
        $resource->nid_no = $request->nid_no;
        if ($request->hasFile('nid_front') && $request->hasFile('nid_back'))
            $resource->nid_image = $this->mergeFrontAndBackNID($profile, $request->file('nid_front'), $request->file('nid_back'));
        $resource->update();
        return $resource;
    }

}