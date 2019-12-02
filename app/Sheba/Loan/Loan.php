<?php

namespace Sheba\Loan;

use App\Models\PartnerBankLoan;
use Sheba\Loan\DS\BusinessInfo;
use Sheba\Loan\DS\Documents;
use Sheba\Loan\DS\FinanceInfo;
use Sheba\Loan\DS\NomineeGranterInfo;
use Sheba\Loan\DS\PartnerLoanRequest;
use Sheba\Loan\DS\PersonalInfo;
use Sheba\Loan\DS\RunningApplication;

class Loan
{
    private $repo;
    private $partner;
    private $data;
    private $profile;
    private $partnerLoanRequest;
    private $resource;

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

    public function create()
    {
    }

    public function update()
    {
    }

    public function validate()
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
        return (new PersonalInfo($this->partner, $this->resource, $this->partnerLoanRequest));
    }

    public function businessInfo()
    {
        return (new BusinessInfo($this->partner, $this->resource));
    }

    public function financeInfo()
    {
        return (new FinanceInfo($this->partner, $this->resource));
    }

    public function nomineeGranter()
    {
        return (new NomineeGranterInfo($this->partner, $this->resource));
    }

    public function documents()
    {
        return (new Documents($this->partner, $this->resource));
    }

    private function isApplicableForLoan($data)
    {
        return Completion::isApplicableForLoan($data);
    }
}
