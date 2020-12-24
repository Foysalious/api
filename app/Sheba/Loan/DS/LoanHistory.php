<?php

namespace Sheba\Loan\DS;

use App\Models\PartnerBankLoan;
use Carbon\Carbon;
use Sheba\Dal\PartnerBankLoan\LoanTypes;

class LoanHistory
{
    /**
     * @var PartnerBankLoan
     */
    private $partnerBankLoan;

    public function __construct(PartnerBankLoan $loan)
    {
        $this->partnerBankLoan = $loan;
    }

    public function toArray()
    {
        $isMicro = $this->isMicro();
        $data    = [
            $this->id(),
            $this->status(),
            $this->loanAmount()
        ];
        if (!$isMicro) {
            $data[] = $this->duration();
        }
        if (!empty($this->partnerBankLoan->bank_id)) {
            $data[] = $this->bankName();
        }
        if ($this->partnerBankLoan->status !== constants('LOAN_STATUS')['approved'] && !$isMicro) {
            $data[] = $this->installment_count();
        }
        $data[] = $this->created_at();
        if ($this->partnerBankLoan->status == constants('LOAN_STATUS')['declined']) {
            $data[] = $this->rejectReason();
        }
        return $data;
    }

    private function isMicro()
    {
        return $this->partnerBankLoan->type == LoanTypes::MICRO;
    }

    private function id()
    {
        return [
            'field' => 'id',
            'key'   => [
                'en' => 'ID',
                'bn' => $this->partnerBankLoan->type == LoanTypes::MICRO ? 'ফ্যাসিলিটি আইডি' : 'লোনের আইডি'
            ],
            'value' => [
                'en' => $this->partnerBankLoan->id,
                'bn' => convertNumbersToBangla($this->partnerBankLoan->id, false)
            ]
        ];
    }

    private function status()
    {
        $content = constants('LOAN_STATUS_BN')[$this->partnerBankLoan->status];
        return [
            'field' => 'status',
            'key'   => [
                'en' => 'Running Status',
                'bn' => 'চলতি আবেদন স্ট্যাটাস'
            ],
            'value' => [
                'en' => preg_replace('/_/', ' ', ucfirst($this->partnerBankLoan->status)),
                'bn' => $content['bn']
            ],
            'color' => $content['color']
        ];
    }

    private function loanAmount()
    {
        if ($this->partnerBankLoan->status != constants('LOAN_STATUSES')['approved']) {
            $en = 'Loan Amount';
            $bn = $this->partnerBankLoan->type == LoanTypes::MICRO ? 'টাকার পরিমাণ' : 'লোনের পরিমাণ';
        } else {
            $en = 'Given Loan Amount';
            $bn = $this->partnerBankLoan->type == LoanTypes::MICRO ? 'বরাদ্দ টাকার পরিমাণ' : 'বরাদ্দ লোনের পরিমাণ';
        }
        return [
            'field' => 'loan_amount',
            'key'   => [
                'en' => $en,
                'bn' => $bn
            ],
            'value' => [
                'en' => $this->partnerBankLoan->loan_amount,
                'bn' => '৳ ' . convertNumbersToBangla(doubleval($this->partnerBankLoan->loan_amount))
            ]
        ];
    }

    private function duration()
    {
        return [
            'field' => 'duration',
            'key'   => [
                'en' => 'Duration',
                'bn' => 'পরিশোধের সময়'
            ],
            'value' => [
                'en' => $this->partnerBankLoan->duration . ' Months',
                'bn' => convertNumbersToBangla((double)$this->partnerBankLoan->duration, false) . ' মাস'
            ]
        ];
    }

    private function bankName()
    {
        $name = $this->partnerBankLoan->bank ? $this->partnerBankLoan->bank->name : $this->partnerBankLoan->bank_name;
        return [
            'field' => 'bank_name',
            'key'   => [
                'en' => 'Bank Name',
                'bn' => $this->partnerBankLoan->type == LoanTypes::MICRO ? 'প্রদানকারীর নাম' : 'ব্যাংকের নাম',
            ],
            'value' => [
                'en' => $this->partnerBankLoan->type == LoanTypes::MICRO ? 'Sheba Platform Limited' : $name,
                'bn' => $this->partnerBankLoan->type == LoanTypes::MICRO ? 'Sheba Platform Limited' : $name
            ]
        ];
    }

    private function installment_count()
    {
        $installment = (int)$this->partnerBankLoan->duration;
        return [
            'field' => 'installment',
            'key'   => [
                'en' => 'Installments',
                'bn' => 'মোট কিস্তি সংখ্যা'
            ],
            'value' => [
                'en' => $installment,
                'bn' => convertNumbersToBangla($installment, false) . ' টি'
            ]
        ];
    }

    private function created_at()
    {
        /** @var Carbon $created_at */
        $created_at = $this->partnerBankLoan->created_at;
        return [
            'field' => 'created_at',
            'key'   => [
                'en' => 'Created at',
                'bn' => 'আবেদনের তারিখ'
            ],
            'value' => [
                'en' => $created_at->format('Y MMM d'),
                'bn' => convertNumbersToBangla($created_at->day, false) . ' ' . banglaMonth($created_at->month) . ' ' . convertNumbersToBangla($created_at->year, false)
            ]
        ];
    }

    private function rejectReason()
    {
        $log    = $this->partnerBankLoan->changeLogs()->where([
            [
                'title',
                'status'
            ],
            [
                'to',
                constants('LOAN_STATUS')['declined']
            ]
        ])->get()->last();
        $reason = $log ? $log->description : "";
        return [
            'field' => 'reject_reason',
            'key'   => [
                'en' => 'Reject Reason',
                'bn' => 'বাতিল করার কারণ'
            ],
            'value' => [
                'en' => $reason,
                'bn' => $reason
            ]
        ];
    }

    private function installment_amount()
    {
        return [
            'field' => 'monthly_installment',
            'key'   => [
                'en' => 'Monthly Installment',
                'bn' => 'কিস্তির পরিমাণ'
            ],
            'value' => [
                'en' => $this->partnerBankLoan->monthly_installment,
                'bn' => '৳ ' . convertNumbersToBangla($this->partnerBankLoan->monthly_installment, false)
            ]
        ];
    }
}
