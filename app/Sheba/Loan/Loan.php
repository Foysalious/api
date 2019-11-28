<?php

namespace Sheba\Loan;

use App\Models\PartnerBankLoan;
use Sheba\Loan\DS\PartnerLoanRequest;
use Sheba\Loan\DS\PersonalInfo;

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

    public static function homepageStatics()
    {
        return [['title' => 'ব্যাংক লোনের সুবিধা কি কি - ', 'list' => ['সহজেই ব্যবসা বার্তা পৌঁছে দিন কাস্টমারের কাছে', 'আপনার সুবিধা মত সময়ে ও বাজেটে স্বল্পমূল্যে কার্যকরী মার্কেটিং ', 'শুধু সফল ভাবে পাঠানো এসএমএস বা ইমেইলের জন্যই মূল্য দিন', 'মার্কেটিং থেকে অর্ডার পাবার রিপোর্ট পাচ্ছেন খুব দ্রুত '], 'list_icon' => ''], ['title' => 'ব্যাংক লোন কিভাবে নেবেন- ', 'list' => ['১। সহজেই ব্যবসা বার্তা পৌঁছে দিন কাস্টমারের কাছে ', '২। আপনার সুবিধা মত সময়ে ও বাজেটে স্বল্পমূল্যে কার্যকরী মার্কেটিং ', '৩। শুধু সফল ভাবে পাঠানো এসএমএস বা ইমেইলের জন্যই মূল্য দিন'], 'list_icon' => '']];
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

    public function personalInfo()
    {
        return (new PersonalInfo($this->partner, $this->resource, $this->partnerLoanRequest));
    }

}
