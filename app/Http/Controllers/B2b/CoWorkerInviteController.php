<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\CoWorker\Requests\Requester as CoWorkerRequester;
use Sheba\Business\CoWorker\Validation\CoWorkerExistenceCheck;
use Sheba\Business\CoWorker\Creator as CoWorkerCreator;
use Sheba\Business\CoWorker\Updater as CoWorkerUpdater;
use Sheba\Business\CoWorker\Requests\BasicRequest;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Repositories\ProfileRepository;
use Sheba\FileManagers\CdnFileManager;
use Sheba\Business\CoWorker\Statuses;
use App\Repositories\FileRepository;
use App\Http\Controllers\Controller;
use Sheba\FileManagers\FileManager;
use Illuminate\Http\JsonResponse;
use Sheba\Reports\ExcelHandler;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Member;
use Carbon\Carbon;
use Exception;

class CoWorkerInviteController extends Controller
{
    use CdnFileManager, FileManager, ModificationFields;

    /** @var FileRepository $fileRepository */
    private $fileRepository;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var BasicRequest $basicRequest */
    private $basicRequest;
    /** @var CoWorkerCreator $coWorkerCreator */
    private $coWorkerCreator;
    /** @var CoWorkerUpdater $coWorkerUpdater */
    private $coWorkerUpdater;
    /** @var CoWorkerRequester $coWorkerRequester */
    private $coWorkerRequester;
    /** @var CoWorkerExistenceCheck $coWorkerExistenceCheck */
    private $coWorkerExistenceCheck;

    public function __construct(FileRepository $file_repository, ProfileRepository $profile_repository,
                                BasicRequest $basic_request, CoWorkerCreator $co_worker_creator,
                                CoWorkerUpdater $co_worker_updater, CoWorkerRequester $coWorker_requester,
                                CoWorkerExistenceCheck $co_worker_existence_check)
    {
        $this->fileRepository = $file_repository;
        $this->profileRepository = $profile_repository;
        $this->basicRequest = $basic_request;
        $this->coWorkerCreator = $co_worker_creator;
        $this->coWorkerUpdater = $co_worker_updater;
        $this->coWorkerRequester = $coWorker_requester;
        $this->coWorkerExistenceCheck = $co_worker_existence_check;
    }

    /**
     * @param $business
     * @param Request $request
     * @param ExcelHandler $excel_handler
     * @return JsonResponse
     * @throws NotAssociativeArray
     * @throws Exception
     */
    public function sendInvitation($business, Request $request, ExcelHandler $excel_handler)
    {
        $this->validate($request, ['emails' => "required"]);

        $business = $request->business;
        $member = $request->manager_member;
        $this->setModifier($member);
        $errors = [];

        $emails = json_decode($request->emails);
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errors, ['email' => $email, 'message' => 'Invalid email address']);
                continue;
            }
            $this->basicRequest->setEmail($email);
            $this->coWorkerCreator->setBusiness($business)->setEmail($email)->setStatus(Statuses::INVITED)->setBasicRequest($this->basicRequest);

            if ($this->coWorkerCreator->hasError()) {
                array_push($errors, ['email' => $email, 'message' => $this->coWorkerCreator->getErrorMessage()]);
                $this->coWorkerCreator->resetError();
                continue;
            }

            #$this->coWorkerCreator->basicInfoStore();
        }

        if ($errors) {
            $file_name = Carbon::now()->timestamp . "_co_worker_invite_error_$business->id.xlsx";
            $file = $excel_handler->setName('Co worker Invite')->setFilename($file_name)->setDownloadFormat('xlsx')->createReport($errors)->save();
            $file_path = $this->saveFileToCDN($file, getCoWorkerInviteErrorFolder(), $file_name);
            unlink($file);

            if ($this->isFailedToCreateAllCoworker($errors, $emails)) {
                return api_response($request, null, 422, [
                    'message' => 'Alert! Invitations failed',
                    'description' => "Invited co-worker/s already exist in the co-worker list. Download the excel file to see details",
                    'link' => $file_path
                ]);
            }

            return api_response($request, null, 303, [
                'message' => 'Alert! Some invitations failed',
                'description' => "Invited co-worker/s already exist in the co-worker list. Download the excel file to see details",
                'link' => $file_path
            ]);
        }

        return api_response($request, null, 200);
    }


    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function sendSingleInvitation($business, Request $request)
    {
        $this->validate($request, ['email' => "required"]);

        /** @var Business $business */
        $business = $request->business;
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        $email = $request->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return api_response($request, null, 420, ['email' => $email, 'message' => 'Invalid email address']);
        }
        $this->basicRequest->setEmail($email);
        $this->coWorkerExistenceCheck->setBusiness($business)->setEmail($email)->checkEmailUsability();

        if ($this->coWorkerExistenceCheck->hasError()) {
            return api_response($request, null, $this->coWorkerExistenceCheck->getErrorCode(), ['message' => $this->coWorkerExistenceCheck->getErrorMessage(), 'business_member_id' => $this->coWorkerExistenceCheck->getBusinessMemberId()]);
        }

        $this->coWorkerCreator->setBasicRequest($this->basicRequest)->setBusiness($business)->setStatus(Statuses::INVITED)->setEmail($email);
        $this->coWorkerCreator->basicInfoStore();
        return api_response($request, null, 200);
    }

    /**
     * @param array $errors
     * @param $emails
     * @return bool
     */
    private function isFailedToCreateAllCoworker(array $errors, $emails)
    {
        return count($errors) == count($emails);
    }

}