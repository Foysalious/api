<?php namespace App\Http\Controllers\Resource;

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
use Sheba\ModificationFields;
use Sheba\Partner\StatusChanger;
use Sheba\Resource\PartnerResourceCreator;

class PersonalInformationController extends Controller
{
    use ModificationFields;

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
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, PartnerResourceCreator $partnerResourceCreator)
    {
        try {
            $request->merge(['mobile' => trim($request->mobile)]);
            $this->validate($request, [
                'nid_no' => 'required_without:resource|string|unique:resources,nid_no',
                'nid_back' => 'required_without:resource|file',
                'nid_front' => 'required_without:resource|file',
                'name' => 'string',
                'address' => 'string',
                'picture' => 'file',
                'resource' => 'numeric',
                'mobile' => 'required_without:resource|string|mobile:bd',
                'additional_mobile' => 'mobile:bd'
            ], ['mobile' => 'Invalid mobile number!', 'unique' => 'Duplicate Nid No!']);
            $partner = $request->partner;
            $this->setModifier($request->manager_resource);
            if ($request->has('resource')) {
                $resource = Resource::find((int)$request->resource);
                $partnerResourceCreator->setPartner($partner);
                $partnerResourceCreator->setData(array(
                    'resource_types' => ['Handyman'],
                    'category_ids' => $partner->categories->pluck('id')->toArray()
                ));
                $partnerResourceCreator->setResource($resource);
                if ($error = $partnerResourceCreator->hasError()) {
                    return api_response($request, 1, 400, ['message' => $error['msg']]);
                }
                $partnerResourceCreator->create();

                if (isPartnerReadyToVerified($partner)) {
                    $status_changer = new StatusChanger($partner, ['status' => constants('PARTNER_STATUSES')['Waiting']]);
                    $status_changer->change();
                }

                return api_response($request, 1, 200);
            } else {
                $partnerResourceCreator->setPartner($partner);
                $partnerResourceCreator->setData(array(
                    'mobile' => $request->mobile,
                    'name' => $request->name,
                    'address' => $request->address,
                    'profile_image' => $request->file('picture'),
                    'category_ids' => $partner->categories->pluck('id')->toArray(),
                    'resource_types' => ['Handyman'],
                    'nid_no' => $request->nid_no,
                    'nid_image' => $this->mergeFrontAndBackNID($request->file('nid_front'), $request->file('nid_back')),
                    'alternate_contact' => $request->has('additional_mobile') ? $request->additional_mobile : null
                ));
                if ($error = $partnerResourceCreator->hasError()) {
                    return api_response($request, 1, 400, ['message' => $error['msg']]);
                }
                $partnerResourceCreator->create();

                if (isPartnerReadyToVerified($partner)) {
                    $status_changer = new StatusChanger($partner, ['status' => constants('PARTNER_STATUSES')['Waiting']]);
                    $status_changer->change();
                }

                return api_response($request, 1, 200);
            }
        } catch ( ValidationException $e ) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function update($resource, Request $request)
    {
        try {
            $resource = $request->resource;
            $profile = $resource->profile;

            $rules = [
                'nid_no' => 'required|string|unique:resources,nid_no,' . $resource->id,
                'name' => 'string',
                'gender' => 'string|in:Male,Female,Other',
                'birthday' => 'date_format:Y-m-d|before:' . date('Y-m-d'),
                'address' => 'string',
                'mobile' => 'string|mobile:bd',
                'additional_mobile' => 'mobile:bd'
            ];

            if (!$profile->pro_pic) {
                $rules['picture'] = 'required|file';
            }

            if (!$resource->nid_image) {
                $rules['nid_back'] = 'required|file';
                $rules['nid_front'] = 'required|file';
            }
            
            $this->validate($request, $rules, ['mobile' => 'Invalid mobile number!', 'unique' => 'Duplicate Nid No!']);
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

            $status_changer = new StatusChanger($request->partner, ['status' => constants('PARTNER_STATUSES')['Waiting']]);
            if (isPartnerReadyToVerified($request->partner)) {
                $status_changer->change();
            }

            return api_response($request, $resource, 200);
        } catch ( ValidationException $e ) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function mergeFrontAndBackNID($front, $back)
    {
        $weight = 227;
        $height = 155;
        $img1 = Image::make($front);
        $img2 = Image::make($back);
        $img1->resize($weight, $height);
        $img2->resize($weight, $height);
        $canvas = Image::canvas(251, 359, '#FCFEFF');
        $canvas->insert($img1, 'top', 12, 17);
        $canvas->insert($img2, 'top', 12, 34 + $height);
        $canvas->encode('png');
        return $canvas;
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
        if ($request->has('nid_no')) $resource->nid_no = $request->nid_no;
        if ($request->has('additional_mobile')) $resource->alternate_contact = formatMobile(trim($request->additional_mobile));
        $profile->update();
        if ($request->hasFile('nid_front') && $request->hasFile('nid_back')) {
            $canvas = $this->mergeFrontAndBackNID($request->file('nid_front'), $request->file('nid_back'));
            $resource->nid_image = $this->fileRepository->uploadImageToCDN('images/resources/nid', Carbon::now()->timestamp . '_' . str_slug($profile->name, '_') . '.png', $canvas);
        }
        $resource->update();
        return $resource;
    }
}