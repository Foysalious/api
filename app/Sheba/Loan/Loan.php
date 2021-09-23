<?php

namespace Sheba\Loan;

use App\Models\BankUser;
use App\Models\Partner;
use App\Models\PartnerBankLoan;
use App\Models\PartnerResource;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\User;
use App\Repositories\FileRepository;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\LoanService;
use App\Sheba\Loan\DLSV2\Exceptions\InsufficientWalletCreditForRepayment;
use App\Sheba\Loan\DLSV2\LoanClaim;
use App\Sheba\Loan\DLSV2\Repayment;
use App\Sheba\Loan\Exceptions\LoanNotFoundException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Accounts\RootAccounts;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\Dal\PartnerBankLoan\Statuses as LoanStatuses;
use Sheba\Dal\LoanClaimRequest\Statuses;
use Sheba\Dal\PartnerBankLoan\LoanTypes;
use Sheba\Dal\Retailer\Retailer;
use Sheba\Dal\StrategicPartnerMember\StrategicPartnerMember;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\FraudDetection\TransactionSources;
use Sheba\HZip;
use Sheba\Loan\DS\BusinessInfo;
use Sheba\Loan\DS\Documents;
use Sheba\Loan\DS\FinanceInfo;
use Sheba\Loan\DS\GranterDetails;
use Sheba\Loan\DS\NomineeGranterInfo;
use Sheba\Loan\DS\PartnerLoanRequest;
use Sheba\Loan\DS\PersonalInfo;
use Sheba\Loan\DS\RunningApplication;
use Sheba\Loan\Exceptions\AlreadyAssignToBank;
use Sheba\Loan\Exceptions\AlreadyRequestedForLoan;
use Sheba\Loan\Exceptions\InsufficientWalletCredit;
use Sheba\Loan\Exceptions\InvalidStatusTransaction;
use Sheba\Loan\Exceptions\InvalidTypeException;
use Sheba\Loan\Exceptions\NotAllowedToAccess;
use Sheba\Loan\Exceptions\NotApplicableForLoan;
use Sheba\Loan\Exceptions\NotShebaPartner;
use Sheba\Loan\Statics\GeneralStatics;
use Sheba\Loan\Validators\RequestValidator;
use Sheba\ModificationFields;
use Sheba\PushNotificationHandler;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class Loan
{
    use CdnFileManager, FileManager, ModificationFields;

    private $repo;
    /** @var Partner $partner */
    private $partner;
    private $data;
    private $profile;
    private $partnerLoanRequest;
    /** @var Resource $resource */
    private $resource;
    private $downloadDir;
    private $zipDir;
    private $user;
    private $finalFields;
    private $type;
    private $fileRepository;
    private $version;
    private $transaction;

    public function __construct(FileRepository $file_repository = null)
    {
        $this->repo           = new LoanRepository();
        $this->downloadDir    = storage_path('downloads');
        $this->zipDir         = public_path('temp/documents.zip');
        $this->user           = request()->user;
        $this->finalFields    = [
            'personal'        => 'personalInfo',
            'business'        => 'businessInfo',
            'finance'         => 'financeInfo',
            'nominee_granter' => 'nomineeGranter',
            'document'        => 'documents'
        ];
        $this->fileRepository = $file_repository;
        $this->type           = LoanTypes::TERM;
    }

    public function setUser($user)
    {
        $this->user = $user;
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

    /**
     * @param $id
     * @return array
     * @throws ReflectionException
     */
    public function get($id)
    {
        /** @var PartnerBankLoan $loan */
        $loan    = $this->repo->find($id);
        $request = new PartnerLoanRequest($loan);
        return $request->toArray();
    }

    /**
     * @param         $loan_id
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
            'big_banner' => GeneralStatics::bigBanner(),
            'banner'     => GeneralStatics::banner(),
        ];
        $data    = array_merge($data, (new RunningApplication($running))->toArray(), ['details' => GeneralStatics::homepage()]);
        return $data;
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function homepageV2()
    {
        $robi_retailer = $this->checkIsRobiRetailer();
        $data          = [
            'big_banner'        => GeneralStatics::bigBanner(),
            'banner'            => GeneralStatics::banner(),
            'robi_retailer'     => $robi_retailer,
            'exception_message' => $robi_retailer ? "" : GeneralStatics::NOT_ROBI_RETAILER_MESSAGE,
            'is_bkash_agent'    => $this->isBkashAgent(),
            'application_fee'   => [
                'term_loan' => GeneralStatics::getFee(LoanTypes::TERM),
                'micro_loan' => GeneralStatics::getFee(LoanTypes::MICRO)
            ]
        ];
        $data          = array_merge($data, GeneralStatics::webViews(), ['running_loan' => $this->getRunningLoan()], ['loan_list' => $this->getApplyLoanList()], ['details' => GeneralStatics::homepage()]);
        return $data;
    }

    /**
     * @throws AlreadyRequestedForLoan|NotApplicableForLoan
     */
    public function apply()
    {
        $this->validate();
        $created = null;
        DB::transaction(function () use (&$created) {
            $applyFee = false;
            if ($this->version === 2) {
                $applyFee = $this->applyFee();
            }
            if ($this->type === LoanTypes::TERM) {
                if (!(isset($this->data['month']) && $this->data['month'])) {
                    $this->data['duration'] = ((int)$this->data['duration'] * 12);
                }
            } else {
                $this->data['duration'] = (int)config('loan.repayment_defaulter_default_duration', 5);
            }
            unset($this->data['month']);
            $created = $this->create();
            if ($applyFee){
                $this->storeJournal($created->id);
            }
        });
        return $created;
    }


    /**
     * @throws AlreadyRequestedForLoan|NotApplicableForLoan
     */
    public function validate()
    {
        $this->validateAlreadyRequested();
        $applicable = $this->getCompletion()['is_applicable_for_loan'];
        if ($this->type === LoanTypes::MICRO && !$this->resource->isAllowedForMicroLoan()) {
            throw new NotApplicableForLoan("Not Allowed to apply for micro loan");
        }
        if (!GeneralStatics::isValidAmount($this->type, (double)$this->data['loan_amount'])) throw new NotApplicableForLoan("Invalid Loan Amount");
        if (!$applicable)
            throw new NotApplicableForLoan();

    }

    /**
     * @throws AlreadyRequestedForLoan
     */
    private function validateAlreadyRequested()
    {
        $requests = $this->repo->where('partner_id', $this->partner->id)->where('type', $this->type ?: LoanTypes::TERM)->get();
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
     * @param $request
     * @throws NotAllowedToAccess
     */
    public function validateRequest($request)
    {
        $loan = $this->repo->find($request->loan_id);

        if ($loan->partner_id != $request->partner->id)
            throw new NotAllowedToAccess();
    }

    /**
     * @return array
     */
    public function getCompletion()
    {
        $data = $this->initiateFinalFields();
        foreach ($data as $key => $val) {
            $data[$key] = $val->completion($this->type);
        }
        $data['is_applicable_for_loan'] = $this->isApplicableForLoan($data);
        if ($this->version === 2) {
            $data['details_link']        = GeneralStatics::getDetailsLink($this->type);
            $data['loan_fee']            = GeneralStatics::getFee($this->type);
            $data['maximum_day']         = GeneralStatics::getMinimumDay($this->type);
            $data['minimum_loan_amount'] = GeneralStatics::getMinimumAmount($this->type);
            $data['maximum_loan_amount'] = GeneralStatics::getMaximumAmount($this->type);
        }

        return $data;
    }


    /**
     * @return array
     */
    private function initiateFinalFields()
    {
        $this->setFinalFields();
        $data = [];
        foreach ($this->finalFields as $key => $val) {
            $data[$key] = $this->$val();
        }
        return $data;
    }

    private function setFinalFields()
    {
        if ($this->version == 2) {
            $this->finalFields = [
                'personal'        => 'personalInfo',
                'business'        => 'businessInfo',
                'finance'         => 'financeInfo',
                'nominee_granter' => 'granterDetails',
                'document'        => 'documents'
            ];
        } else {
            $this->finalFields = [
                'personal'        => 'personalInfo',
                'business'        => 'businessInfo',
                'finance'         => 'financeInfo',
                'nominee_granter' => 'nomineeGranter',
                'document'        => 'documents'
            ];
        }
    }

    private function isApplicableForLoan(&$data)
    {
        return Completion::isApplicableForLoan($data, $this->type, $this->version);
    }

    public function create()
    {
        $data       = $this->data;
        $final_info = [];
        foreach ($this->finalFields as $key => $val) {
            $final_info[$key] = $this->$val()->toArray();
        }
        $data['final_information_for_loan'] = json_encode($final_info);
        if ($this->type === LoanTypes::MICRO) {
            $data['bank_id'] = config('loan.micro_loan_assigned_bank_id');
        }
        return (new PartnerLoanRequest())->setPartner($this->partner)->create($data);
    }


    /**
     * @param $request
     * @return bool
     */
    public function claim($request)
    {
        $data = [
            'loan_id'     => $request->loan_id,
            'resource_id' => $request->manager_resource->id,
            'amount'      => $request->amount,
            'status'      => Statuses::PENDING,
            'log'         => '৳' . convertNumbersToBangla($request->amount, true, 0) . ' টাকা দাবি করা হয়েছে',
        ];

        return (new LoanClaim())->createRequest($data);
    }

    /**
     * @param      $loan_id
     * @param bool $all
     * @param null $month
     * @param null $year
     * @return mixed
     */
    public function claimList($loan_id, $all = false, $month = null, $year = null)
    {
        if (!$all)
            $claims = (new LoanClaim())->getByYearAndMonth($loan_id, $month, $year);
        else
            $claims = (new LoanClaim())->getAll($loan_id);

        $pending_claim         = (new LoanClaim())->getPending($loan_id);
        $data['claim_list']    = [];
        $data['pending_claim'] = null;

        if ($pending_claim) {
            $data['pending_claim']['id']         = $pending_claim->id;
            $data['pending_claim']['status']     = $pending_claim->status;
            $data['pending_claim']['amount']     = $pending_claim->amount;
            $data['pending_claim']['log']        = $pending_claim->log;
            $data['pending_claim']['created_at'] = Carbon::parse($pending_claim->created_at)->format('Y-m-d H:i:s');
        }

        foreach ($claims as $claim) {
            array_push($data['claim_list'], [
                'id'         => $claim->id,
                'status'     => $claim->status,
                'amount'     => $claim->amount,
                'log'        => $claim->log,
                'created_at' => Carbon::parse($claim->created_at)->format('Y-m-d H:i:s')
            ]);
        }

        return $data;
    }


    /**
     * @param $request
     * @return bool
     */
    public function canClaim($request)
    {
        $can_claim    = true;
        $partner_loan = $this->repo->find($request->loan_id);
        $last_claim   = (new LoanClaim())->setLoan($request->loan_id)->lastClaim();

        if (($partner_loan->status != LoanStatuses::DISBURSED) || ($request->amount > $partner_loan->loan_amount) || ($last_claim && ($last_claim->status == Statuses::PENDING || ($last_claim->status == Statuses::APPROVED && !$this->hasClearedDue($last_claim->id)))))
            $can_claim = false;
        return $can_claim;
    }

    /**
     * @param $claim_id
     * @return bool
     */
    public function hasClearedDue($claim_id)
    {
        return $this->getDue($claim_id) > 0 ? false : true;
    }

    /**
     * @param $claim_id
     * @return mixed
     */
    public function getDue($claim_id)
    {
        return (new Repayment())->setClaim($claim_id)->getDue();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function approvedClaimMsgSeen($request)
    {
        $last_claim = (new LoanClaim())->setLoan($request->loan_id)->lastClaim();
        return (new LoanClaim())->setClaim($last_claim->id)->updateApprovedMsgSeen($request->success_msg_seen);
    }

    /**
     * @param $request
     * @return bool
     * @throws \Exception
     */
    public function claimStatusUpdate($request)
    {
        return (new LoanClaim())->setLoan($request->loan_id)->setClaim($request->claim_id)->updateStatus($request->from, $request->to, $request->user);
    }

    public function personalInfo()
    {
        return (new PersonalInfo($this->partner, $this->resource, $this->partnerLoanRequest));
    }

    public function businessInfo()
    {
        return (new BusinessInfo($this->partner, $this->resource))->setType($this->type)->setVersion($this->version);
    }

    public function financeInfo()
    {
        $finance = (new FinanceInfo($this->partner, $this->resource));
        return $finance;
    }

    public function nomineeGranter()
    {
        $nominee_granter = (new NomineeGranterInfo($this->partner, $this->resource));
        return $nominee_granter;
    }

    public function granterDetails()
    {
        $granter = (new GranterDetails($this->partner, $this->resource));
        return $granter;
    }

    public function documents()
    {
        $document = (new Documents($this->partner, $this->resource));
        return $document;
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

    /**
     * @param Request $request
     * @return object
     */
    public function microLoanData(Request $request)
    {
        $from_date      = $request->from_date ?: date("Y-m-01");
        $to_date        = $request->to_date ?: date("Y-m-d");
        $data           = $this->getMicroLoans($request->user, $from_date, $to_date);
        $statuses       = constants('LOAN_STATUS');
        $formatted_data = (object)[
            'applied_loan'       => count($data),
            'loan_rejected'      => 0,
            'loan_disburse'      => 0,
            'loan_approved'      => 0,
            'loan_closed'        => 0,
            'total_registration' => $this->getRegisteredRetailerCount($from_date, $to_date)
        ];
        foreach ($data as $loan) {
            if ($loan["status"] === $statuses["declined"]) {
                $formatted_data->loan_rejected++;
            }
            if ($loan["status"] === $statuses["approved"]) {
                $formatted_data->loan_approved++;
            }
            if ($loan["status"] === $statuses["disbursed"]) {
                $formatted_data->loan_disburse++;
            }
            if ($loan["status"] === $statuses["closed"]) {
                $formatted_data->loan_closed++;
            }
        }
        return $formatted_data;
    }

    /**
     * @param $from
     * @param $to
     * @return mixed
     */
    private function getRegisteredRetailerCount($from, $to)
    {
        $retailers = Retailer::whereHas('profile', function ($q) {
            $q->has('resource');
        })->get();

        $resource_ids = [];
        foreach ($retailers as $retailer)
            array_push($resource_ids, $retailer->profile->resource->id);

        $all_registered_partners_ids = PartnerResource::whereIn('resource_id', $resource_ids)->distinct()->pluck('partner_id');
        return Partner::whereIn('id', $all_registered_partners_ids)->whereBetween('created_at', [$from, $to])->count();

    }

    private function getLoans($user)
    {
        $bank_id = null;
        if ($user instanceof BankUser)
            $bank_id = $user->bank->id;
        $query = $this->repo;
        if ($bank_id) {
            $query = $query->where('partner_bank_loans.bank_id', $bank_id);
        }
        return $query->with(['bank'])->get();
    }

    private function getMicroLoans($user, $from_date, $to_date)
    {

        $bank_id = null;
        $query   = $this->repo;

        if ($user instanceof BankUser) {
            $bank_id = $user->bank->id;
            if ($bank_id) {
                $query = $query->whereBetween('created_at', [$from_date . " 00:00:00", $to_date . " 23:59:59"])
                               ->where('partner_bank_loans.bank_id', $bank_id)
                               ->where('type', LoanTypes::MICRO);
            }
        }

        if ($user instanceof StrategicPartnerMember) {
            $query = $query->whereBetween('created_at', [$from_date . " 00:00:00", $to_date . " 23:59:59"])
                           ->where('type', LoanTypes::MICRO);
        }

        return $query->get();
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
        (new RequestValidator($request))->validate();
        $loan                   = (new PartnerLoanRequest($request));
        $details                = $loan->details();
        $details['next_status'] = $loan->getNextStatus($loan_id);
        return $details;
    }

    /**
     * @param $loan_id
     * @return array
     * @throws NotAllowedToAccess
     * @throws ReflectionException
     * @throws LoanNotFoundException
     */
    public function showForAgent($loan_id)
    {
        /** @var PartnerBankLoan $request */
        $request = $this->repo->find($loan_id);
        if (empty($request))
            throw new LoanNotFoundException();
        (new RequestValidator($request))->validate();
        $loan = (new PartnerLoanRequest($request));
        return $loan->detailsForAgent();
    }

    /**
     * @param         $loan_id
     * @param Request $request
     * @throws NotAllowedToAccess
     * @throws ReflectionException
     */
    public function uploadDocument($loan_id, Request $request)
    {
        /** @var PartnerBankLoan $loan */
        $loan = $this->repo->find($loan_id);
        $user = $this->user;
        (new RequestValidator($loan))->validate();
        (new DocumentUploader($loan))->setUser($user)->setFor($request->for)->update($request);

    }

    /**
     * @param         $loan_id
     * @param Request $request
     * @throws InvalidStatusTransaction
     * @throws NotAllowedToAccess
     */
    public function statusChange($loan_id, Request $request)
    {
        /** @var PartnerBankLoan $partner_bank_loan */
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
        DB::transaction(function () use ($partner_bank_loan, $request, $old_status, $new_status, $description, $user) {
            $partner_bank_loan->update();
            (new PartnerLoanRequest($partner_bank_loan))->storeChangeLog($request->user, 'status', $old_status, $new_status, $description);
            $title      = "Loan status has been updated from $old_status to $new_status";
            $class      = class_basename($partner_bank_loan);
            $event_type = "App\\Models\\$class";
            $event_id   = $partner_bank_loan->id;
            Notifications::sendLoanNotification($title, $event_type, $event_id);
            if ($new_status == LoanStatuses::APPROVED || $new_status == LoanStatuses::DISBURSED || $new_status == LoanStatuses::DECLINED) {
                if ($partner_bank_loan->type == LoanTypes::MICRO)
                    Notifications::sendStatusChangeNotification($old_status, $new_status, $partner_bank_loan);
                $reason = $new_status == LoanStatuses::DECLINED ? $description : null;
                Notifications::sendStatusChangeSms($partner_bank_loan, $new_status, $reason, $user);
            }

        });
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
        $resource = $profile ? $profile->resource : null;
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
            'duration'    => $config['minimum_duration'],
            'type'        => $request->type ? $request->type : 0
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
        ])->orderBy('created_at', 'ASC')->first();
        if (!empty($changeLog))
            return $changeLog->created_at;
        return null;
    }

    /**
     * @return int
     */
    private function checkIsRobiRetailer()
    {
        return $this->partner->retailers->where('strategic_partner_id', 2)->count() ? 1 : 0;
    }

    /**
     * @return int
     */
    private function isBkashAgent()
    {
        return $this->partner->bkash_account_type == "agent" ? 1 : 0;
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getApplyLoanList()
    {
        $running_loans   = $this->getRunningLoan();
        $apply_loan_list = GeneralStatics::loanList();
        $apply_statuses  = [LoanStatuses::WITHDRAWAL, LoanStatuses::REJECTED, LoanStatuses::DECLINED, LoanStatuses::CLOSED];
        foreach ($running_loans as $running_loan) {
            if (!in_array($running_loan['data']['status'], $apply_statuses)) {
                foreach ($apply_loan_list as $key => $loan) {
                    if ($running_loan['data']['type'] == $loan['loan_type']) {
                        unset($apply_loan_list[$key]);
                        $apply_loan_list = array_values($apply_loan_list);
                    }
                }
            }
        }
        return $apply_loan_list;
    }

    /**
     * @return array|array[]
     * @throws ReflectionException
     */
    private function getRunningLoan()
    {
        $running_term_loan  = !$this->partner->loan()->type(LoanTypes::TERM)->get()->isEmpty() ? $this->partner->loan()->type(LoanTypes::TERM)->get()->last()->toArray() : [];
        $running_micro_loan = !$this->partner->loan()->type(LoanTypes::MICRO)->get()->isEmpty() ? $this->partner->loan()->type(LoanTypes::MICRO)->get()->last()->toArray() : [];
        $running_loan_data  = [];
        if (count($running_term_loan))
            $running_loan_data[] = $this->getRunningLoanData($running_term_loan, GeneralStatics::RUNNING_TERM_LOAN_ICON, GeneralStatics::TERM_TITLE_BD);
        if (count($running_micro_loan))
            $running_loan_data[] = $this->getRunningLoanData($running_micro_loan, GeneralStatics::RUNNING_MICRO_LOAN_ICON, GeneralStatics::MICRO_TITLE_BD);

        return $running_loan_data;
    }

    /**
     * @param $running_loan
     * @param $icon_url
     * @param $title_bn
     * @return array|array[]
     * @throws ReflectionException
     */
    private function getRunningLoanData($running_loan, $icon_url, $title_bn)
    {
        $unit_en  = $running_loan["type"] == LoanTypes::MICRO ? " days" : " years";
        $unit_bn  = $running_loan["type"] == LoanTypes::MICRO ? " দিন" : " বছর";
        $duration = $running_loan["type"] == LoanTypes::MICRO ? $running_loan["duration"] : $running_loan["duration"] / 12;
        return [
            "data"          => (new RunningApplication($running_loan))->toArray(),
            "icon"          => $icon_url,
            "title_bn"      => $title_bn,
            "loan_duration" => [
                "duration_en" => $duration . $unit_en,
                "duration_bn" => en2bnNumber($duration) . $unit_bn
            ]
        ];
    }

    public function uploadRetailerList($request)
    {
        $uploaded_csv = $request->file('retailers');
        $filename     = 'robi_retailers_' . Carbon::now()->timestamp . '.' . $uploaded_csv->extension();
        $this->fileRepository->uploadToCDN($filename, $uploaded_csv, 'dls_v2/robi/retailer_list/');

        $mobiles = [];
        Excel::load($uploaded_csv, function ($reader) use (&$mobiles) {
            $results = $reader->get();
            $mobiles = $results->map(function ($results) {
                return formatMobile($results['mobile']);
            });
        });


        $existing_mobiles = Retailer::where('strategic_partner_id', $request->strategic_partner_id)->pluck('mobile');
        $to_insert        = array_unique(array_diff($mobiles->toArray(), $existing_mobiles->toArray()));
        $to_insert        = collect($to_insert)->map(function ($to_insert) use ($request) {
            return [
                'strategic_partner_id' => $request->strategic_partner_id,
                'mobile'               => $to_insert,
                'created_by'           => $request->user->profile->id,
                'created_by_name'      => $request->user->profile->name,
                'created_at'           => Carbon::now()
            ];
        });
        Retailer::insert($to_insert->toArray());
    }


    /**
     * @param mixed $version
     * @return Loan
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param mixed $type
     * @return Loan
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     * @throws InvalidTypeException
     * @throws InsufficientWalletCredit
     */
    private function applyFee()
    {
        if ($this->version !== 2) return true;
        if (!in_array($this->type, LoanTypes::get())) throw new InvalidTypeException();
        $fee = (double)GeneralStatics::getFee($this->type);
        if ($fee > 0 && (double)$this->partner->wallet >= $fee) {
            $this->setModifier($this->resource);
            $this->transaction = (new WalletTransactionHandler())->setModel($this->partner)->setAmount($fee)->setSource(TransactionSources::LOAN_FEE)->setType(Types::debit())->setLog("$fee BDT has been collected from {$this->resource->profile->name} as Loan Application fee for $this->type loan")->store();
            return true;
        }
        if ($fee > 0) throw  new InsufficientWalletCredit();
        return false;
    }

    private function storeJournal($loanId){
        $fee = (double)GeneralStatics::getFee($this->type);
        (new JournalCreateRepository())->setTypeId($this->partner->id)
            ->setSource($this->transaction)
            ->setAmount($fee)
            ->setDebitAccountKey((new Accounts())->expense->loan_service::LOAN_SERVICE_CHARGE)
            ->setCreditAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
            ->setDetails("Loan fee charge")
            ->setReference($loanId)
            ->store();
    }
}
