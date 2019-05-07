<?php namespace App\Http\Controllers\B2b;

use App\Models\Driver;
use App\Models\Profile;
use App\Models\Vehicle;
use App\Models\VehicleRegistrationInformation;
use App\Repositories\FileRepository;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;
use DB;

class DriversController extends Controller
{
    use CdnFileManager, FileManager;
    use ModificationFields;

    private $fileRepository;

    public function __construct(FileRepository $file_repository)
    {
        $this->fileRepository = $file_repository;
    }

    public function store($member, Request $request)
    {
        try {
            $this->validate($request, [
                'license_number' => 'required',
                'license_number_image' => 'required|mimes:jpeg,png',
                'license_class' => 'required',
                'years_of_experience' => 'required|integer',
            ]);

            $member = Member::find($member);
            $this->setModifier($member);

            $driver_data = [
                'license_number' => $request->license_number,
                'license_number_image' => $this->updateDocuments('license_number_image', $request->file('license_number_image')),
                'license_class' => $request->license_class,
                'years_of_experience' => $request->years_of_experience,
            ];
            $driver = Driver::create($this->withCreateModificationField($driver_data));
            $profile = Profile::where('mobile', formatMobile($request->mobile))->first();
            if (!$profile) {
                $this->createDriverProfile($member, $driver, $request);
            } else {
                $profile_data = [
                    'driver_id' => $driver->id,
                ];
                $profile->update($this->withCreateModificationField($profile_data));
            }

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function createDriverProfile($member, $driver, Request $request)
    {
        $this->setModifier($member);
        $profile_data = [
            'remember_token' => str_random(255),
            'mobile' => !empty($request->mobile) ? formatMobile($request->mobile) : null,
            'name' => $request->name,
            'driver_id' => $driver->id,
            'address' => $request->address,
            'email' => $request->email,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'nid_no' => $request->nid_no,
            'pro_pic' => $this->updateProfilePicture('pro_pic', $request->file('pro_pic')),
        ];

        return Profile::create($this->withCreateModificationField($profile_data));
    }

    public function update($member, $driver, Request $request)
    {
        try {
            $this->validate($request, [
                'license_number' => 'required',
                'license_number_image' => 'required|mimes:jpeg,png',
                'license_class' => 'required',
                'years_of_experience' => 'required|integer',
            ]);

            $member = Member::find($member);
            $this->setModifier($member);
            $driver = Driver::find((int)$driver);

            $driver_data = [
                'license_number' => $request->license_number,
                'license_number_image' => $this->updateDocuments('license_number_image', $request->file('license_number_image')),
                'license_class' => $request->license_class,
                'years_of_experience' => $request->years_of_experience,
            ];
            $driver->update($this->withUpdateModificationField($driver_data));
            #$profile = Profile::where('mobile', formatMobile($request->mobile))->first();

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function driverLists($member, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            list($offset, $limit) = calculatePagination($request);
            $drivers = Driver::select('id', 'status')->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();

            $driver_lists = [];
            foreach ($drivers as $driver) {
                $profile = $driver->profile;
                $vehicle = $driver->vehicle;
                $basic_information = $vehicle ? $vehicle->basicInformations : null;
                $driver = [
                    'picture' => $profile->pro_pic,
                    'mobile' => $profile->mobile,
                    'status' => $driver->status,
                    'model_year' => $basic_information ? Carbon::parse($basic_information->model_year)->format('Y') : null,
                    'model_name' => $basic_information ? $basic_information->model_name : null,
                    'vehicle_type' => $basic_information ? $basic_information->type : null,
                ];
                array_push($driver_lists, $driver);
            }
            return api_response($request, $driver_lists, 200, ['driver_lists' => $driver_lists]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDriverGeneralInfo($member, $driver, Request $request)
    {
        try {
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            #dd($driver->profile);

            $general_info = [
                'driver_id' => $driver->id,
                'name' => $profile->name,
                'dob' => Carbon::parse($profile->dob)->format('j M, Y'),
                #'blood_group' => $profile->blood_group,
                'nid_no' => $profile->nid_no,
            ];

            return api_response($request, $general_info, 200, ['general_info' => $general_info]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateDriverGeneralInfo($member, $driver, Request $request)
    {
        try {
            $this->validate($request, [
                'dob' => 'required|date|date_format:Y-m-d',
                'name' => 'string',
                'nid_no' => 'string',
            ]);
            $member = Member::find($member);
            $business = $member->businesses->first();
            $this->setModifier($member);

            $driver = Driver::find((int)$driver);
            $profile = $driver->profile;
            $general_info = [
                'name' => $request->name,
                'dob' => $request->dob,
                #'blood_group' => $request->blood_group,
                'nid_no' => $request->nid_no,
            ];
            $profile->update($this->withUpdateModificationField($general_info));

            return api_response($request, 1, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function updateDocuments($image_for, $photo)
    {
        $vehicle_registration_information = new VehicleRegistrationInformation();

        if (basename($vehicle_registration_information->image_for) != 'default.jpg') {
            $filename = substr($vehicle_registration_information->{$image_for}, strlen(config('sheba.s3_url')));
            $this->deleteOldImage($filename);
        }

        #$picture_link = $this->fileRepository->uploadToCDN($this->makePicName($vehicle_registration_information, $photo, $image_for), $photo, 'images/drivers/' . $image_for . '_');
        $picture_link = $this->fileRepository->uploadToCDN($this->makeDocumentsName($vehicle_registration_information, $photo, $image_for), $photo, 'images/profiles/' . $image_for . '_');
        return $picture_link;
    }

    private function updateProfilePicture($image_for, $photo)
    {
        $profile = new Profile();

        if (basename($profile->image_for) != 'default.jpg') {
            $filename = substr($profile->{$image_for}, strlen(config('sheba.s3_url')));
            $this->deleteOldImage($filename);
        }

        $picture_link = $this->fileRepository->uploadToCDN($this->makePicName($profile, $photo, $image_for), $photo, 'images/profiles/' . $image_for . '_');
        return $picture_link;
    }


    private function makeDocumentsName(VehicleRegistrationInformation $vehicle_registration_information, $photo, $image_for = 'license_number_image')
    {
        return $filename = Carbon::now()->timestamp . '_' . $image_for . '_image_' . $vehicle_registration_information->id . '.' . $photo->extension();
    }

    private function makePicName(Profile $profile, $photo, $image_for = 'license_number_image')
    {
        return $filename = Carbon::now()->timestamp . '_' . $image_for . '_image_' . $profile->id . '.' . $photo->extension();
    }

    private function deleteOldImage($filename)
    {
        $old_image = substr($filename, strlen(config('sheba.s3_url')));
        $this->fileRepository->deleteFileFromCDN($old_image);
    }

}
