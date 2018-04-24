<?php

namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
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
                'nid_no' => $resource->nid_no,
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($resource, Request $request)
    {
        try {
            $this->validate($request, [
                'nid_no' => "required|string",
                'nid_back' => 'required|file',
                'nid_front' => 'required|file',
                'name' => "sometimes|required|string",
                'gender' => 'sometimes|required|string|in:Male,Female,Other',
                'birthday' => 'sometimes|required|date_format:Y-m-d|before:' . date('Y-m-d'),
                'address' => 'sometimes|required|string',
                'picture' => 'sometimes|required|file',

            ]);
            $resource = $request->resource;
            $profile = $resource->profile;
            if ($request->hasFile('picture')) {
                $picture = $request->file('picture');
                $pro_pic = $this->fileRepository->uploadToCDN($this->makeProfilePicName($profile, $picture), $picture, 'images/profiles/');
            }
            $this->mergeFrontAndBackNID($profile, $request->file('nid_front'), $request->file('nid_back'));
            $profile->update(array_merge($request->only(['name', 'gender', 'address']), ['dob' => $request->birthday, ['pro_pic' => $pro_pic]]));
            $nid_image_link = $this->mergeFrontAndBackNID($profile, $request->file('nid_front'), $request->file('nid_back'));
            $resource->update(['nid_no' => $request->nid_no, 'nid_image' => $nid_image_link]);
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
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

}