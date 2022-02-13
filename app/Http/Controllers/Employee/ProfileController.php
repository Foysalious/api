<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\CoWorker\ProfileInformation\EmergencyInfoUpdater;
use App\Sheba\Business\CoWorker\ProfileInformation\EmployeeType;
use App\Sheba\Business\CoWorker\ProfileInformation\OfficialInfoUpdater;
use App\Sheba\Business\CoWorker\ProfileInformation\PersonalInfoUpdater;
use App\Sheba\Business\CoWorker\ProfileInformation\ProfileRequester;
use App\Sheba\Business\CoWorker\ProfileInformation\ProfileUpdater;
use App\Transformers\Business\EmergencyContactInfoTransformer;
use App\Transformers\Business\FinancialInfoTransformer;
use App\Transformers\Business\OfficialInfoTransformer;
use App\Transformers\Business\PersonalInfoTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Gender\Gender;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class ProfileController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    /*** @var BusinessMember $businessMember*/
    private $businessMember;
    /*** @var ProfileRequester $profileRequester */
    private $profileRequester;
    /*** @var MemberRepositoryInterface $memberRepository*/
    private $memberRepository;

    public function __construct()
    {
        $this->memberRepository = app(MemberRepositoryInterface::class);
        $this->businessMember = app(BusinessMember::class);
        $this->profileRequester = app(ProfileRequester::class);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getFinancialInfo(Request $request): JsonResponse
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_member, new FinancialInfoTransformer());
        $employee_financial_details = $manager->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['financial_info' => $employee_financial_details]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOfficialInfo(Request $request): JsonResponse
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_member, new OfficialInfoTransformer());
        $employee_official_details = $manager->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['official_info' => $employee_official_details]);
    }

    /**
     * @param Request $request
     * @param ProfileUpdater $profile_updater
     * @return JsonResponse
     */
    public function updateEmployee(Request $request, ProfileUpdater $profile_updater): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|string',
            'department' => 'required|string',
            'designation' => 'required|string',
            'joining_date' => 'required|date',
            'gender' => 'required|string|in:' . Gender::implodeEnglish()
        ]);

        try {
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);
            $member = $this->getMember($request);
            $this->setModifier($member);

            $this->profileRequester->setBusinessMember($business_member)
                ->setName($request->name)
                ->setEmail($request->email)
                ->setDepartment($request->department)
                ->setDesignation($request->designation)
                ->setJoiningDate($request->joining_date)
                ->setGender($request->gender);

            if ($this->profileRequester->hasError()) return api_response($request, null, $this->profileRequester->getErrorCode(), ['message' => $this->profileRequester->getErrorMessage()]);

            $profile_updater->setProfileRequester($this->profileRequester)->update();

            return api_response($request, null, 200);
        } catch (Throwable $e) {
            return api_response($request, null, 420, ['message' => 'You are not eligible employee']);
        }
    }

    /**
     * @param Request $request
     * @param OfficialInfoUpdater $official_info_updater
     * @return JsonResponse
     */
    public function updateOfficialInfo(Request $request, OfficialInfoUpdater $official_info_updater): JsonResponse
    {
        $this->validate($request, [
            'manager' => 'required|numeric',
            'employee_type' => 'required|string|in:' . implode(',', EmployeeType::get()),
            'employee_id' => 'required',
            'grade' => 'required'
        ]);

        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $this->memberRepository->find($business_member['member_id']);
        $this->setModifier($member);

        $this->profileRequester
            ->setBusinessMember($business_member)
            ->setManager($request->manager)
            ->setEmployeeType($request->employee_type)
            ->setEmployeeId($request->employee_id)
            ->setGrade($request->grade);

        $official_info_updater->setProfileRequester($this->profileRequester)->update();

        return api_response($request, null, 200);

    }

    /**
     * @param Request $request
     * @param EmergencyInfoUpdater $emergency_info_updater
     * @return JsonResponse
     */
    public function updateEmergencyInfo(Request $request, EmergencyInfoUpdater $emergency_info_updater): JsonResponse
    {
        $this->validate($request, [
            'name' => 'sometimes|required|string',
            'mobile' => 'sometimes|required|mobile:bd',
            'relationship' => 'sometimes|required|string',
        ]);

        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $this->memberRepository->find($business_member['member_id']);
        $this->setModifier($member);

        $this->profileRequester
            ->setBusinessMember($business_member)
            ->setEmergencyContactName($request->name)
            ->setEmergencyContactMobile($request->mobile)
            ->setEmergencyContactRelation($request->relationship);

        $emergency_info_updater->setProfileRequester($this->profileRequester)->update();

        return api_response($request, null, 200);

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEmergencyContactInfo(Request $request): JsonResponse
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_member, new EmergencyContactInfoTransformer());
        $employee_emergency_details = $manager->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['emergency_contact_info' => $employee_emergency_details]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getPersonalInfo(Request $request): JsonResponse
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_member, new PersonalInfoTransformer());
        $employee_emergency_details = $manager->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['emergency_contact_info' => $employee_emergency_details]);
    }

    /**
     * @param Request $request
     * @param PersonalInfoUpdater $personal_info_updater
     * @return JsonResponse
     */
    public function updatePersonalInfo(Request $request, PersonalInfoUpdater $personal_info_updater): JsonResponse
    {
        $validation_data = [
            'mobile' => 'mobile:bd',
            'dob' => 'date',
        ];

        $validation_data['nid_front'] = $this->isFile($request->nid_front) ? 'sometimes|required|mimes:jpg,jpeg,png,pdf' : 'sometimes|required|string';
        $validation_data['nid_back'] = $this->isFile($request->nid_back) ? 'sometimes|required|mimes:jpg,jpeg,png,pdf' : 'sometimes|required|string';
        $validation_data['passport_image'] = $this->isFile($request->passport_image) ? 'sometimes|required|mimes:jpg,jpeg,png,pdf' : 'sometimes|required|string';

        $this->validate($request, $validation_data);

        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $member = $this->memberRepository->find($business_member['member_id']);
        $this->setModifier($member);

        $this->profileRequester
            ->setBusinessMember($business_member)
            ->setMobile($request->mobile)
            ->setDateOfBirth($request->dob)
            ->setAddress($request->address)
            ->setNationality($request->nationality)
            ->setNidNo($request->nid_no)
            ->setPassportNo($request->passport_no)
            ->setBloodGroup($request->blood_group)
            ->setSocialLinks($request->social_links)
            ->setNidFrontImage($request->nid_front)
            ->setNidBackImage($request->nid_back)
            ->setPassportImage($request->passport_image);

        if ($this->profileRequester->hasError()) return api_response($request, null, $this->profileRequester->getErrorCode(), ['message' => $this->profileRequester->getErrorMessage()]);

        $personal_info_updater->setProfileRequester($this->profileRequester)->update();

        return api_response($request, null, 200);
    }

    /**
     * @param $file
     * @return bool
     */
    private function isFile($file): bool
    {
        if ($file instanceof Image || $file instanceof UploadedFile) return true;
        return false;
    }
}