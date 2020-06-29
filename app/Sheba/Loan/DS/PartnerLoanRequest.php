<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\PartnerBankLoan;
use App\Models\PartnerSubscriptionPackage;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sheba\ModificationFields;

class PartnerLoanRequest implements Arrayable
{
    use ModificationFields;
    public $partnerBankLoan;
    /** @var Partner */
    public $partner;
    public $bank;
    public $loan_amount;
    public $status;
    public $duration;
    public $monthly_installment;
    public $interest_rate;
    /** @var LoanRequestDetails $final_details */
    public $final_details;
    public $created_by;
    public $updated_by;
    public $created;
    public $updated;

    public function __construct(PartnerBankLoan $request = null)
    {
        $this->partnerBankLoan = $request;
        if ($this->partnerBankLoan) {
            $this->setPartner($this->partnerBankLoan->partner);
            $this->setDetails();
        }
    }

    public function setDetails()
    {
        $this->final_details = new LoanRequestDetails($this);
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
     * @return PartnerLoanRequest
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param PartnerBankLoan $partnerBankLoan
     * @return PartnerLoanRequest
     */
    public function setPartnerBankLoan($partnerBankLoan)
    {
        $this->partnerBankLoan = $partnerBankLoan;
        return $this;
    }

    public function create($data)
    {
        if(!isset($data['type']) || !$data['type'])
            $data['type'] = 'term';
        $data['partner_id'] = $this->partner->id;
        $data['status'] = constants('LOAN_STATUS')['applied'];
        $data['interest_rate'] = (int)constants('LOAN_CONFIG')['interest'];
        $duration = (int)$data['duration'];
        $data['monthly_installment'] = emi_calculator($data['interest_rate'], $data['loan_amount'], $duration);
        $this->setModifier($this->partner);
        $this->partnerBankLoan = new PartnerBankLoan($this->withCreateModificationField($data));
        $this->setDetails();
        $this->partnerBankLoan->save();
        return $this->partnerBankLoan;
    }

    public function __get($name)
    {
        if ($this->partnerBankLoan) {
            return $this->partnerBankLoan->{$name};
        } else {
            return $this->{$name};
        }
    }

    public function history()
    {
        return [
            'id' => $this->partnerBankLoan->id,
            'details' => (new LoanHistory($this->partnerBankLoan))->toArray()
        ];

    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function details()
    {
        return $this->toArray();
    }

    public function detailsForAgent()
    {
        return $this->toArrayForAgent();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     * @throws \ReflectionException
     */
    public function toArray()
    {
        $bank = $this->partnerBankLoan->bank()->select('name', 'id', 'logo')->first();
        $output = $this->getNextStatus($this->partnerBankLoan->id);
        $generated_id = ($bank ? $bank->id : '000') . '-' . str_pad($this->partnerBankLoan->id, 8 - strlen($this->partnerBankLoan->id), '0', STR_PAD_LEFT);
        $admin_resource = $this->partner->getAdmin();
        return [
            'id' => $this->partnerBankLoan->id,
            'generated_id' => $generated_id,
            'partner' => [
                'id' => $this->partner->id,
                'current_package' =>  PartnerSubscriptionPackage::find($this->partner->package_id)->show_name,
                'name' => $this->partner->name,
                'logo' => $this->partner->logo,
                'updated_at' => (Carbon::parse($this->partner->updated_at))->format('j F, Y h:i A'),
                'profile' => [
                    'name' => $this->partner->getContactPerson(),
                    'mobile' => $this->partner->getContactNumber(),
                    'is_nid_verified' => $this->partner->isNIDVerified() ? true : false,
                    'dob' => $admin_resource ? $admin_resource->profile->dob : null,
                    'updated_at' => (Carbon::parse($this->partner->updatedAt()))->format('j F, Y h:i A'),
                ]
            ],
            'credit_score' => $this->partnerBankLoan->credit_score,
            'purpose' => $this->partnerBankLoan->purpose,
            'bank' => $bank ? $bank->toArray() : [
                'name' => null,
                'id' => null,
                'logo' => null
            ],
            'duration' => $this->partnerBankLoan->duration,
            'interest_rate' => $this->partnerBankLoan->interest_rate,
            'status' => [
                'name' => ucfirst(preg_replace('/_/', ' ', $this->partnerBankLoan->status)),
                'status' => $this->partnerBankLoan->status
            ],
            'monthly_installment' => $this->partnerBankLoan->monthly_installment,
            'loan_amount' => $this->partnerBankLoan->loan_amount,
            'total_installment' => (int)$this->partnerBankLoan->duration,
            'status_' => constants('LOAN_STATUS_BN')[$this->partnerBankLoan->status],
            'final_information_for_loan' => $this->final_details->toArray(),
            'next_status' => $output
        ];
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     * @throws \ReflectionException
     */
    public function toArrayForAgent()
    {
        $bank = $this->partnerBankLoan->bank()->select('name', 'id', 'logo')->first();
        $generated_id = ($bank ? $bank->id : '000') . '-' . str_pad($this->partnerBankLoan->id, 8 - strlen($this->partnerBankLoan->id), '0', STR_PAD_LEFT);
        return [
            'id' => $this->partnerBankLoan->id,
            'generated_id' => $generated_id,
            'partner' => [
                'id' => $this->partner->id,
                'name' => $this->partner->name,
                'logo' => $this->partner->logo,
                'updated_at' => (Carbon::parse($this->partner->updated_at))->format('j F, Y h:i A'),
                'profile' => [
                    'name' => $this->partner->getContactPerson(),
                    'mobile' => $this->partner->getContactNumber(),
                    'updated_at' => (Carbon::parse($this->partner->updatedAt()))->format('j F, Y h:i A'),
                ]
            ],

            'purpose' => $this->partnerBankLoan->purpose,
            'status' => [
                'name' => ucfirst(preg_replace('/_/', ' ', $this->partnerBankLoan->status)),
                'status' => $this->partnerBankLoan->status
            ],
            'status_' => constants('LOAN_STATUS_BN')[$this->partnerBankLoan->status],
            'document'             => $this->getDocumentsForAgents(),

        ];
    }

    public function getNextStatus($loan_id)
    {
        $status_res = [
            'considerable' => 'applied',
            'applied' => 'submitted',
            'submitted' => 'verified',
            'verified' => 'approved',
            'approved' => 'sanction_issued',
            'sanction_issued' => 'disbursed',
            'disbursed' => 'closed',
            'rejected' => 'closed',
        ];
        $all = [
            'declined',
            'hold',
            'withdrawal'
        ];
        if ($this->partnerBankLoan->status == 'declined')
            $new_status = []; else if ($this->partnerBankLoan->status == 'withdrawal')
            $new_status = []; else if ($this->partnerBankLoan->status == 'disbursed')
            $new_status = ['closed']; else if ($this->partnerBankLoan->status == 'closed')
            $new_status = []; else if ($this->partnerBankLoan->status == 'hold') {
            $change_log = $this->partnerBankLoan->changeLogs()->where(function ($q) {
                return $q->where('to', 'hold')->orWhere('to', '["hold"]');
            })->orderby('id', 'desc')->first();
            $status_before_hold = 'applied';
            if ($change_log) {
                $status_before_hold = $change_log['from'];
            }
            $new_status = array_merge([$status_res[$status_before_hold]], [
                'declined',
                'withdrawal'
            ]);
        } else
            $new_status = array_merge([$status_res[$this->partnerBankLoan->status]], $all);
        $output = [];
        foreach ($new_status as $status) {
            $output[] = [
                'name' => ucfirst(preg_replace('/_/', ' ', $status)),
                'status' => $status,
                'extras' => constants('LOAN_STATUS_BN')[$status]
            ];
        }
        return $output;

    }

    public function listItem()
    {
        $bank = $this->partnerBankLoan->bank()->select('name', 'id', 'logo')->first();
        return [
            'id' => $this->partnerBankLoan->id,
            'generated_id' => $bank->id . '-' . str_pad($this->partnerBankLoan->id, 8 - strlen($this->partnerBankLoan->id), '0', STR_PAD_LEFT),
            'created_at' => $this->partnerBankLoan->created_at->format('d M, Y'),
            'name' => $this->partnerBankLoan->partner->getContactPerson(),
            'phone' => $this->partnerBankLoan->partner->getContactNumber(),
            'partner' => $this->partnerBankLoan->partner->name,
            'status' => ucfirst(preg_replace('/_/', ' ', $this->partnerBankLoan->status)),
            'status_' => constants('LOAN_STATUS_BN')[$this->partnerBankLoan->status],
            'created_by' => $this->partnerBankLoan->created_by,
            'updated_by' => $this->partnerBankLoan->updated_by,
            'created_by_name' => $this->partnerBankLoan->created_by_name,
            'updated_by_name' => $this->partnerBankLoan->updated_by_name,
            'updated' => $this->partnerBankLoan->updated_at->format('d M, Y'),
            'bank' => $bank ? $bank->toArray() : null
        ];
    }

    public function storeChangeLog($user, $title, $from, $to, $description)
    {
        $this->setModifier($user);
        return $this->partnerBankLoan->changeLogs()->create($this->withCreateModificationField([
            'title' => $title,
            'from' => $from,
            'to' => $to,
            'description' => $description
        ]));
    }

    public function getDocuments()
    {
        return $this->final_details->getDocuments();
    }

    public function getDocumentsForAgents()
    {
        return $this->final_details->getDocumentsForAgents();
    }
}
