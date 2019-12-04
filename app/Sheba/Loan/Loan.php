<?php

namespace Sheba\Loan;

use App\Models\BankUser;
use App\Models\PartnerBankLoan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Loan\DS\BusinessInfo;
use Sheba\Loan\DS\Documents;
use Sheba\Loan\DS\FinanceInfo;
use Sheba\Loan\DS\NomineeGranterInfo;
use Sheba\Loan\DS\PartnerLoanRequest;
use Sheba\Loan\DS\PersonalInfo;
use Sheba\Loan\DS\RunningApplication;
use Sheba\Loan\Exceptions\AlreadyAssignToBank;
use Sheba\Loan\Exceptions\AlreadyRequestedForLoan;
use Sheba\Loan\Exceptions\NotApplicableForLoan;
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

    public function __construct()
    {
        $this->repo = new LoanRepository();
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

    public function update()
    {
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function homepage()
    {
        $running = !$this->partner->loan->isEmpty() ? $this->partner->loan->last()->toArray() : [];
        $data    = [
            'big_banner' => config('sheba.s3_url') . 'images/offers_images/banners/loan_banner_v3_1440_628.jpg',
            'banner'     => config('sheba.s3_url') . 'images/offers_images/banners/loan_banner_v3_720_324.jpg',
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
                    'সহজেই ব্যবসা বার্তা পৌঁছে দিন কাস্টমারের কাছে',
                    'আপনার সুবিধা মত সময়ে ও বাজেটে স্বল্পমূল্যে কার্যকরী মার্কেটিং ',
                    'শুধু সফল ভাবে পাঠানো এসএমএস বা ইমেইলের জন্যই মূল্য দিন',
                    'মার্কেটিং থেকে অর্ডার পাবার রিপোর্ট পাচ্ছেন খুব দ্রুত '
                ],
                'list_icon' => 'icon'
            ],
            [
                'title'     => 'ব্যাংক লোন কিভাবে নেবেন- ',
                'list'      => [
                    'সহজেই ব্যবসা বার্তা পৌঁছে দিন কাস্টমারের কাছে ',
                    'আপনার সুবিধা মত সময়ে ও বাজেটে স্বল্পমূল্যে কার্যকরী মার্কেটিং ',
                    'শুধু সফল ভাবে পাঠানো এসএমএস বা ইমেইলের জন্যই মূল্য দিন'
                ],
                'list_icon' => 'number'
            ]
        ];
    }

    /**
     * @throws NotApplicableForLoan
     * @throws \ReflectionException
     * @throws AlreadyRequestedForLoan
     */
    public function apply()
    {
        $this->validate();
        $data       = $this->data;
        $fields     = [
            'personal',
            'business',
            'finance',
            'nominee_granter',
            'document'
        ];
        $final_info = [];
        foreach ($fields as $val) {
            $final_info[$val] = $this->$val->toArray();
        }
        $data['final_information_for_loan'] = json_encode($final_info);
        return (new PartnerLoanRequest())->setPartner($this->partner)->create($data);
    }

    /**
     * @throws AlreadyRequestedForLoan
     * @throws NotApplicableForLoan
     * @throws \ReflectionException
     */
    public function validate()
    {
        $requests = $this->repo->where('partner_id', $this->partner->id)->get();
        if (!$requests->isEmpty()) {
            $last_request = $requests->last();
            $statuses     = constants('LOAN_STATUS');
            if (in_array($last_request->status, [
                $statuses['approved'],
                $statuses['considerable']
            ])) {
                throw new AlreadyRequestedForLoan();
            }
        }
        $applicable = $this->getCompletion()['is_applicable_for_loan'];
        if (!$applicable)
            throw new NotApplicableForLoan();

    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getCompletion()
    {
        $data                           = [
            'personal'  => $this->personalInfo()->completion(),
            'business'  => $this->businessInfo()->completion(),
            'finance'   => $this->financeInfo()->completion(),
            'nominee'   => $this->nomineeGranter()->completion(),
            'documents' => $this->documents()->completion()
        ];
        $data['is_applicable_for_loan'] = $this->isApplicableForLoan($data);
        return $data;
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

    private function isApplicableForLoan($data)
    {
        return Completion::isApplicableForLoan($data);
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
        return $this->filterList($request, $output);
    }

    private function filterList(Request $request, Collection $output)
    {
        if ($request->has('q')) {
            $output = $output->filter(function ($item) use ($request) {
                $query = strtolower($request->q);
                return str_contains(strtolower($item['name']), $query) || str_contains($item['phone'], $query) || str_contains(strtolower($item['partner']), $query) || str_contains(strtolower($item['bank']['name']), $query);
            })->values();
        }
        if ($request->has('date')) {
            $output = $output->filter(function ($item) use ($request) {
                $date      = Carbon::parse($request->date)->format('Y-m-d');
                $item_date = Carbon::parse($item->created_at)->format('Y-m-d');
                return $date == $item_date;
            })->values();
        }
        return $output;
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
     * @throws \ReflectionException
     */
    public function show($loan_id)
    {
        /** @var PartnerBankLoan $request */
        $request = $this->repo->find($loan_id);
        return (new PartnerLoanRequest($request))->details();
    }

    /**
     * @param $loan_id
     * @param Request $request
     * @param $user
     * @throws \ReflectionException
     */
    public function uploadDocument($loan_id, Request $request, $user)
    {
        /** @var PartnerBankLoan $loan */
        $loan           = $this->repo->find($loan_id);
        $picture        = $request->file('picture');
        $name           = $request->name;
        $formatted_name = strtolower(preg_replace("/ /", "_", $name));
        list($extra_file, $extra_file_name) = $this->makeExtraLoanFile($picture, $formatted_name);
        $url                                                                         = $this->saveImageToCDN($extra_file, getTradeLicenceImagesFolder(), $extra_file_name);
        $detail                                                                      = (new PartnerLoanRequest($loan))->details();
        $detail['final_information_for_loan']['document']['extras'][$formatted_name] = $url;
        $this->setModifier($user);
        DB::transaction(function () use ($loan, $detail, $formatted_name, $user) {
            $loan->update($this->withUpdateModificationField([
                'final_information_for_loan' => json_encode($detail['final_information_for_loan'])
            ]));
            $loan->changeLogs()->create($this->withCreateModificationField([
                'title'       => 'extra_image',
                'from'        => 'none',
                'to'          => $formatted_name,
                'description' => 'Extra image added'
            ]));
        });

    }
}
