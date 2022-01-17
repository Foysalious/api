<?php namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\FileRepository;
use Sheba\Gender\Gender;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use DB;
use Sheba\ModificationFields;
use Sheba\Partner\StatusChanger;
use Sheba\Resource\Creator\ResourceCreateRequest;
use Sheba\Resource\PartnerResourceCreator;
use Throwable;

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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, PartnerResourceCreator $partnerResourceCreator, ResourceCreateRequest $resourceCreateRequest)
    {
        try {
            $request->merge(['mobile' => trim($request->mobile)]);
            $this->validate($request, [
                'nid_no' => 'string|unique:resources,nid_no',
                'nid_back' => 'file|mimes:jpeg,png,jpg',
                'nid_front' => 'file|mimes:jpeg,png,jpg',
                'birth_date' => 'date|date_format:Y-m-d|before:' . Carbon::today()->subYears(18)->format('Y-m-d'),
                'name' => 'string',
                'address' => 'string',
                'picture' => 'file|mimes:jpeg,png,jpg',
                'resource' => 'numeric',
                'mobile' => 'required_without:resource|string|mobile:bd',
                'additional_mobile' => 'mobile:bd'
            ], ['mobile' => 'Invalid mobile number!', 'unique' => 'Duplicate Nid No!']);
            $this->setModifier($request->manager_resource);

            $partner = $request->partner;
            $resource_types = isset($request->resource_types) ? explode(',', $request->resource_types) : ['Handyman'];

            if ($request->has('resource')) {
                $resource = Resource::find((int)$request->resource);
                $partnerResourceCreator->setPartner($partner);
                $partnerResourceCreator->setData(['resource_types' => $resource_types, 'category_ids' => $partner->categories->pluck('id')->toArray()]);
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
                $resourceCreateRequest->setNidNo($request->nid_no)->setNidFrontImage($request->file('nid_front'))->setNidBackImage($request->file('nid_back'))
                    ->setProfilePicture($request->file('picture'))->setBirthDate($request->birth_date);
                $partnerResourceCreator->setResourceCreateRequest($resourceCreateRequest)->setData(array(
                    'mobile' => $request->mobile,
                    'name' => $request->name,
                    'address' => $request->address,
                    'category_ids' => $partner->categories->pluck('id')->toArray(),
                    'resource_types' => $resource_types,
                    'nid_no' => $request->nid_no,
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
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $resource
     * @param Request $request
     * @return JsonResponse
     */
    public function update($resource, Request $request)
    {
        try {
            $resource = $request->resource;
            $partner = $request->partner;
            $profile = $resource->profile;
            $rules = [
                'nid_no' => 'string|unique:resources,nid_no,' . $resource->id,
                'name' => 'string',
                'gender' => 'string|in:' . Gender::implodeEnglish(),
                'birthday' => 'date_format:Y-m-d|before:' . date('Y-m-d'),
                'address' => 'string',
                'mobile' => 'string|mobile:bd',
                'additional_mobile' => 'mobile:bd'
            ];
            if (!$profile->pro_pic) {
                $rules['picture'] = 'file';
            }
            if (!$resource->nid_image) {
                $rules['nid_back'] = 'file';
                $rules['nid_front'] = 'file';
            }

            $this->validate($request, $rules, ['mobile' => 'Invalid mobile number!', 'unique' => 'Duplicate Nid No!']);

            if ($resource->is_verified) {
                return api_response($request, null, 400, ['message' => "Verified resource can't be updated."]);
            }
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
            if ($request->has('resource_types')) {
                $request->resource_types = explode(',', $request->resource_types);
                $newly_requested_types = array_diff($request->resource_types, $resource->partnerResources->pluck('resource_type')->toArray());
                if ($resource_cap_error = $this->hasResourceCapError($newly_requested_types, $partner)) {
                    return api_response($request, null, 400, ['message' => $resource_cap_error]);
                }
                if (!in_array('Admin', $request->resource_types) && $partner->admins->count() == 1 && ($partner->admins->first()->id == $resource->id)) {
                    return api_response($request, null, 400, ['message' => "There Is no admin in your company, at least one admin need to run your company"]);
                }
            }

            $resource = $this->updateInformation($request, $profile, $resource, $partner);

            $status_changer = new StatusChanger($partner, ['status' => constants('PARTNER_STATUSES')['Waiting']]);
            if (isPartnerReadyToVerified($partner)) {
                $status_changer->change();
            }

            return api_response($request, $resource, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $types
     * @param Partner $partner
     * @return string|null
     */
    private function hasResourceCapError($types, Partner $partner)
    {
        return !$partner->canCreateResource(is_array($types) ? $types : [$types]) ?
            'You Have Reached Maximum Resource Limit. Please Update Your Subscription Package' : null;
    }

    private function mergeFrontAndBackNID($front, $back)
    {
        $img1 = Image::make($front);
        $img2 = Image::make($back);
        $width = 1920;
        $first_image_height = (int)(($width / $img1->height()) * $img1->height());
        $img1->resize($width, $first_image_height);
        $second_image_height = (int)(($width / $img2->height()) * $img2->height());
        $img2->resize($width, $second_image_height);
        $canvas = Image::canvas(2000, $first_image_height + $second_image_height + 50, '#FCFEFF');
        $canvas->insert($img1, 'top', 12, 17);
        $canvas->insert($img2, 'top', 12, 34 + $first_image_height);
        $canvas->encode('png');
        return $canvas;
    }

    private function makeProfilePicName($profile, $photo)
    {
        return $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id . '.' . $photo->extension();
    }

    /**
     * @param Request $request
     * @param Profile $profile
     * @param $resource
     * @param $partner
     * @return mixed
     */
    private function updateInformation(Request $request, Profile $profile, $resource, $partner)
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
        if ($request->has('resource_types')) $this->associatePartnerResource($request, $resource, $partner);

        return $resource;
    }

    /**
     * Associate Partner with Resource on partner_resource
     *
     * @param Request $request
     * @param $resource
     * @param Partner $partner
     */
    public function associatePartnerResource(Request $request, $resource, Partner $partner)
    {
        $old_partner_resource_type = $resource->partnerResources->where('partner_id', $partner->id)->pluck('resource_type', 'id')->toArray();
        $new_partner_resource_type = $request->resource_types;
        $removed_partner_resource_type = array_diff($old_partner_resource_type, $new_partner_resource_type);
        $added_partner_resource_type = array_diff($new_partner_resource_type, $old_partner_resource_type);
        PartnerResource::destroy(array_keys($removed_partner_resource_type));

        foreach ($added_partner_resource_type as $resource_type) {
            $partner->resources()->save($resource, $this->pivotData($resource_type));
        }
    }

    /**
     * Get the pivot data for a resource type
     *
     * @param $type
     * @return array
     */
    public function pivotData($type)
    {
        return $this->withBothModificationFields(['resource_type' => constants('RESOURCE_TYPES')[$type]]);
    }
}