<?php

namespace Sheba\Loan;

use App\Models\BankUser;
use App\Models\Partner;
use App\Models\PartnerBankLoan;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\HZip;
use Sheba\Loan\DS\BusinessInfo;
use Sheba\Loan\DS\Documents;
use Sheba\Loan\DS\FinanceInfo;
use Sheba\Loan\DS\NomineeGranterInfo;
use Sheba\Loan\DS\PartnerLoanRequest;
use Sheba\Loan\DS\PersonalInfo;
use Sheba\Loan\DS\RunningApplication;
use Sheba\Loan\Exceptions\AlreadyAssignToBank;
use Sheba\Loan\Exceptions\AlreadyRequestedForLoan;
use Sheba\Loan\Exceptions\InvalidStatusTransaction;
use Sheba\Loan\Exceptions\NotAllowedToAccess;
use Sheba\Loan\Exceptions\NotApplicableForLoan;
use Sheba\Loan\Exceptions\NotShebaPartner;
use Sheba\ModificationFields;

class Loan
{
    use CdnFileManager, FileManager, ModificationFields;
    private $repo;
    private $partner;
    private $data;
    private $profile;
    private $partnerLoanRequest;
    private $resource;
    private $personal;
    private $finance;
    private $business;
    private $nominee_granter;
    private $document;
    private $downloadDir;
    private $zipDir;
    private $user;
    private $finalFields;

    public function __construct()
    {
        $this->repo        = new LoanRepository();
        $this->downloadDir = storage_path('downloads');
        $this->zipDir      = public_path('temp/documents.zip');
        $this->user        = request()->user;
        $this->finalFields = [
            'personal'        => 'personalInfo',
            'business'        => 'businessInfo',
            'finance'         => 'financeInfo',
            'nominee_granter' => 'nomineeGranter',
            'document'        => 'documents'
        ];
    }

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     * @return Loan
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $resource
     * @return Loan
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return Loan
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param mixed $partner
     * @return Loan
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function get($id)
    {
        /** @var PartnerBankLoan $loan */
        $loan    = $this->repo->find($id);
        $request = new PartnerLoanRequest($loan);
        return $request->toArray();
    }

    /**
     * @param $loan_id
     * @param Request $request
     * @throws NotAllowedToAccess
     * @throws ReflectionException
     */
    public function update($loan_id, Request $request)
    {
        /** @var PartnerBankLoan $loan */
        $loan = $this->repo->find($loan_id);
        $user = $this->user;
        if (!empty($user) && (!($user instanceof User) && ($user instanceof BankUser && $user->bank->id != $loan->bank_id))) {
            throw new NotAllowedToAccess();
        }
        $loanRequest = (new PartnerLoanRequest($loan));
        $details     = $loanRequest->details();
        // $new_data = json_decode($request->get('data'),true);
        $new_data = $request->get('data');
        $updater  = (new Updater($details, $new_data));
        DB::transaction(function () use ($updater, $loanRequest, $request, $details, $new_data) {
            $difference = $updater->findDifference()->getDifference();
            $updater->update($loanRequest, $request);
            if (!empty($difference)) {
                $loanRequest->storeChangeLog($request->user, json_encode(array_column($difference, 'title')), json_encode(array_column($difference, 'old')), json_encode(array_column($difference, 'new')), 'Loan Request Updated');
            }
        });

    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function homepage()
    {
        $running = !$this->partner->loan->isEmpty() ? $this->partner->loan->last()->toArray() : [];
        $data    = [
            'big_banner' => config('sheba.s3_url') . 'images/offers_images/banners/loan_banner_v5_1440_628.jpg',
            'banner'     => config('sheba.s3_url') . 'images/offers_images/banners/loan_banner_v5_720_324.jpg',
        ];
        $data    = array_merge($data, (new RunningApplication($running))->toArray());
        $data    = array_merge($data, ['details' => self::homepageStatics()]);
        return $data;
    }

    public static function homepageStatics()
    {
        return [
            [
                'title'     => 'ব্যাংক লোনের সুবিধা কি কি - ',
                'list'      => [
                    'সহজ শর্তে লোন নিন',
                    'জামানত বিহীন লোন নিন',
                    'ঘরে বসেই লোনের আবেদন করুন',
                    'ঘরে বসেই লোন পরিশোধ করুন'
                ],
                'list_icon' => 'icon'
            ],
            [
                'title'     => 'ব্যাংক লোন কিভাবে নেবেন- ',
                'list'      => [
                    'sManager অ্যাপ থেকে প্রয়োজনীয় সকল তথ্য পুরন করুন',
                    'লোন ক্যলকুলেটর দিয়ে হিসাব করে কিস্তির ধারনা নিন',
                    'লোনের আবেদন নিশ্চিত করুন',
                    'সেবা ও ব্যঙ্ক থেকে যাচাই করার পরে খুব দ্রুত আপনার কাছে লোন পৌঁছে যাবে'
                ],
                'list_icon' => 'number'
            ]
        ];
    }

    /**
     * @throws NotApplicableForLoan
     * @throws ReflectionException
     * @throws AlreadyRequestedForLoan
     */
    public function apply()
    {
        $this->validate();
        return $this->create();
    }

    /**
     * @throws AlreadyRequestedForLoan
     * @throws NotApplicableForLoan
     * @throws ReflectionException
     */
    public function validate()
    {
        $this->validateAlreadyRequested();
        $applicable = $this->getCompletion()['is_applicable_for_loan'];
        if (!$applicable)
            throw new NotApplicableForLoan();

    }

    /**
     * @throws AlreadyRequestedForLoan
     */
    private function validateAlreadyRequested()
    {
        $requests = $this->repo->where('partner_id', $this->partner->id)->get();
        if (!$requests->isEmpty()) {
            $last_request = $requests->last();
            $statuses     = constants('LOAN_STATUS');
            if (!in_array($last_request->status, [
                $statuses['closed'],
                $statuses['withdrawal'],
                $statuses['rejected'],
                $statuses['hold'],
                $statuses['declined']
            ])) {
                throw new AlreadyRequestedForLoan();
            }
        }
    }

    /**
     * @return array
     */
    public function getCompletion()
    {
        $data = $this->initiateFinalFields();
        foreach ($data as $key => $val) {
            $data[$key] = $val->completion();
        }
        $data['is_applicable_for_loan'] = $this->isApplicableForLoan($data);
        return $data;
    }

    /**
     * @return array
     */
    private function initiateFinalFields()
    {
        $data = [];
        foreach ($this->finalFields as $key => $val) {
            $data[$key] = $this->$val();
        }
        return $data;
    }

    private function isApplicableForLoan(&$data)
    {
        return Completion::isApplicableForLoan($data);
    }

    public function create()
    {
        $data       = $this->data;
        $final_info = [];
        foreach ($this->finalFields as $key => $val) {
            $final_info[$key] = $this->$key->toArray();
        }
        $data['final_information_for_loan'] = json_encode($final_info);
        return (new PartnerLoanRequest())->setPartner($this->partner)->create($data);
    }

    public function personalInfo()
    {
        $this->personal = (new PersonalInfo($this->partner, $this->resource, $this->partnerLoanRequest));
        return $this->personal;
    }

    public function businessInfo()
    {
        $this->business = (new BusinessInfo($this->partner, $this->resource));
        return $this->business;
    }

    public function financeInfo()
    {
        $this->finance = (new FinanceInfo($this->partner, $this->resource));
        return $this->finance;
    }

    public function nomineeGranter()
    {
        $this->nominee_granter = (new NomineeGranterInfo($this->partner, $this->resource));
        return $this->nominee_granter;
    }

    public function documents()
    {
        $this->document = (new Documents($this->partner, $this->resource));
        return $this->document;
    }

    public function history()
    {
        $loans = $this->partner->loan;
        if ($loans->isEmpty())
            return [];
        $history = [];
        foreach ($loans as $loan) {
            $loanRequest = new PartnerLoanRequest($loan);
            $history[]   = $loanRequest->setPartner($this->partner)->history();
        }
        return $history;
    }

    public function all(Request $request)
    {

        $user    = $request->user;
        $bank_id = null;
        if ($user instanceof BankUser)
            $bank_id = $user->bank->id;
        $query = $this->repo;
        if ($bank_id) {
            $query = $query->where('partner_bank_loans.bank_id', $bank_id);
        }
        $data   = $query->with(['bank'])->get();
        $output = collect();
        foreach ($data as $loan) {
            $output->push((new PartnerLoanRequest($loan))->listItem());
        }
        $output = $output->sortByDesc('id');
        return $this->filterList($request, $output);
    }

    private function filterList(Request $request, Collection $output)
    {
        if ($request->has('q')) {
            $output = $output->filter(function ($item) use ($request) {
                $query = strtolower($request->q);
                return str_contains(strtolower($item['name']), $query) || str_contains($item['phone'], $query) || str_contains(strtolower($item['partner']), $query) || str_contains(strtolower($item['bank']['name']), $query);
            });
        }
        if ($request->has('date')) {
            $output = $output->filter(function ($item) use ($request) {
                $date      = Carbon::parse($request->date)->format('Y-m-d');
                $item_date = Carbon::parse($item->created_at)->format('Y-m-d');
                return $date == $item_date;
            });
        }
        if ($request->has('status')) {
            $output = $output->filter(function ($item) use ($request) {
                return $item['status'] == $request->status;
            });
        }
        return $output->values();
    }

    /**
     * @param $loan_id
     * @param $bank_id
     * @throws AlreadyAssignToBank
     */
    public function assignBank($loan_id, $bank_id)
    {
        $model = $this->repo->find($loan_id);
        if ($model->bank_id)
            throw new AlreadyAssignToBank();
        $this->repo->update($model, ['bank_id' => $bank_id]);
    }

    /**
     * @param $loan_id
     * @return array
     * @throws NotAllowedToAccess
     * @throws ReflectionException
     */
    public function show($loan_id)
    {
        /** @var PartnerBankLoan $request */
        $request = $this->repo->find($loan_id);
        $user    = $this->user;
        if (!empty($user) && (!($user instanceof User) && ($user instanceof BankUser && $user->bank->id != $request->bank_id))) {
            throw new NotAllowedToAccess();
        }
        $loan                   = (new PartnerLoanRequest($request));
        $details                = $loan->details();
        $details['next_status'] = $loan->getNextStatus($loan_id);
        return $details;
    }

    /**
     * @param $loan_id
     * @param Request $request
     * @throws NotAllowedToAccess
     * @throws ReflectionException
     */
    public function uploadDocument($loan_id, Request $request)
    {
        /** @var PartnerBankLoan $loan */
        $loan = $this->repo->find($loan_id);
        $user = $this->user;
        if (!empty($user) && (!($user instanceof User) && ($user instanceof BankUser && $user->bank->id != $loan->bank_id))) {
            throw new NotAllowedToAccess();
        }
        (new DocumentUploader($loan))->setUser($user)->setFor($request->for)->update($request);

    }

    /**
     * @param $loan_id
     * @param Request $request
     * @throws InvalidStatusTransaction
     * @throws NotAllowedToAccess
     */
    public function statusChange($loan_id, Request $request)
    {

        $partner_bank_loan = $this->repo->find($loan_id);
        $user              = $this->user;
        if (!empty($user) && (!($user instanceof User) && ($user instanceof BankUser && $user->bank->id != $partner_bank_loan->bank_id))) {
            throw new NotAllowedToAccess();
        }
        $old_status  = $partner_bank_loan->status;
        $new_status  = $request->new_status;
        $description = $request->has('description') ? $request->description : 'Status Changed';
        $status      = [
            'applied',
            'submitted',
            'verified',
            'approved',
            'sanction_issued',
            'disbursed',
            'closed'
        ];
        $old_index   = array_search($old_status, $status);
        $new_index   = array_search($new_status, $status);
        if (!(($old_status == 'hold') || $new_index - $old_index == 1 || (in_array($new_status, [
                    'declined',
                    'hold',
                    'withdrawal'
                ]) && (!in_array($old_status, [
                    'disbursed',
                    'closed',
                    'declined',
                    'withdrawal'
                ]))))) {
            throw new InvalidStatusTransaction();
        }
        $partner_bank_loan->status = $new_status;
        DB::transaction(function () use ($partner_bank_loan, $request, $old_status, $new_status, $description) {
            $partner_bank_loan->update();
            (new PartnerLoanRequest($partner_bank_loan))->storeChangeLog($request->user, 'status', $old_status, $new_status, $description);
            $title      = "Loan status has been updated from $old_status to $new_status";
            $class      = class_basename($partner_bank_loan);
            $event_type = "App\\Models\\$class";
            $event_id   = $partner_bank_loan->id;
            $this->sendLoanNotification($title, $event_type, $event_id);
        });


    }

    private function sendLoanNotification($title, $event_type, $event_id)
    {
        notify()->departments([
            9,
            13
        ])->send([
            "title"      => $title,
            'link'       => env('SHEBA_BACKEND_URL') . '/sp-loan',
            "type"       => notificationType('Info'),
            "event_type" => $event_type,
            "event_id"   => $event_id
        ]);
    }

    /**
     * @param $loan_id
     * @return bool|string
     * @throws NotAllowedToAccess
     */
    public function downloadDocuments($loan_id)
    {
        /** @var PartnerBankLoan $loan */
        $loan = $this->repo->find($loan_id);
        $user = $this->user;
        if (!empty($user) && (!($user instanceof User) && ($user instanceof BankUser && $user->bank->id != $loan->bank_id))) {
            throw new NotAllowedToAccess();
        }
        $documents = (new PartnerLoanRequest($loan))->getDocuments();
        $flat      = new RecursiveIteratorIterator(new RecursiveArrayIterator($documents));
        $files     = HZip::downloadFiles($flat, $this->downloadDir);
        if (!empty($files)) {
            $dir  = $this->zipDir();
            $link = $this->saveFileToCDN($dir, getLoanDocumentsFolder() . '/' . $loan_id, $this->uniqueFileName($dir, 'documents.zip'));
            unlink($dir);
            return $link;
        } else {
            return false;
        }
    }

    private function zipDir()
    {

        HZip::zipDir($this->downloadDir, $this->zipDir);
        return $this->zipDir;
    }

    public function downloadFromUrl($url)
    {
        $file = public_path('temp');
        $f    = HZip::downLoadFile($url, $file);
        return $f ? $file . '/' . basename($url) : false;
    }

    /**
     * @param Request $request
     * @return PartnerBankLoan
     * @throws NotShebaPartner
     * @throws NotAllowedToAccess
     * @throws AlreadyRequestedForLoan
     */
    public function createNew(Request $request)
    {
        if (!($this->user instanceof BankUser) && !($this->user instanceof User))
            throw new NotAllowedToAccess();
        $mobile  = formatMobile($request->mobile);
        $profile = Profile::where('mobile', $mobile)->first();
        /** @var Resource $resource */
        $resource = $profile->resource;
        if (empty($profile) || empty($resource))
            throw new NotShebaPartner();
        /** @var Partner $partner */
        $partner = $profile->resource->firstPartner();
        if (empty($partner) || !$resource->isManager($partner) || !$resource->isAdmin($partner)) {
            throw new NotShebaPartner();
        }
        $config = constants('LOAN_CONFIG');
        $data   = [
            'loan_amount' => $config['minimum_amount'],
            'duration'    => $config['minimum_duration']
        ];
        if ($this->user instanceof BankUser) {
            $data['bank_id'] = $this->user->bank->id;
        }
        $request = $this->setPartner($partner)->setResource($resource)->setData($data);
        $this->validateAlreadyRequested();
        $this->initiateFinalFields();
        return $request->create();
    }

    /**
     * @param $loan_id
     * @return mixed|null
     */
    public function getSanctionIssueDate($loan_id)
    {
        /** @var PartnerBankLoan $loan */
        $loan      = $this->repo->find($loan_id);
        $changeLog = $loan->changeLogs()->where([
            [
                'title',
                'status'
            ],
            [
                'to',
                'sanction_issued'
            ]
        ])->last();
        if (!empty($changeLog))
            return $changeLog->created_at;
        return null;
    }
}
