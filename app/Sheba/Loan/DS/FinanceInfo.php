<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\PartnerBankInformation;
use App\Models\Resource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use ReflectionException;
use Sheba\Loan\Completion;
use Sheba\ModificationFields;

class FinanceInfo implements Arrayable
{
    use ModificationFields;
    private $partner;
    private $resource;
    /** @var LoanRequestDetails */
    private $loanDetails;
    private $profile;
    private $bank_information;

    public function __construct(Partner $partner = null, Resource $resource = null, LoanRequestDetails $loanRequest = null)
    {
        $this->partner     = $partner;
        $this->resource    = $resource;
        $this->loanDetails = $loanRequest;
        if ($this->resource) {
            $this->profile = $resource->profile;
        }
        if ($this->partner) {
            $this->bank_information = $partner->bankInformations;
        }
    }

    public static function getValidators(Request $request = null)
    {
        return [
            'acc_name' => 'required|string',
            'acc_no' => 'required|string',
            'bank_name' => 'required|string',
            'branch_name' => 'required|string',
            'acc_type' => $request && $request->loan_type == constants('LOAN_TYPE')["micro_loan"] ? 'required|string|in:savings,current' : "string|in:savings,current",
            'bkash_no' => $request && $request->loan_type == constants('LOAN_TYPE')["micro_loan"] ? 'required|string|mobile:bd' : "string|mobile:bd",
            'bkash_account_type' => $request && $request->loan_type == constants('LOAN_TYPE')["micro_loan"] ? 'required|string|in:personal,agent,merchant' : "string|in:personal,agent,merchant"
        ];
    }

    /**
     * @param Request $request
     * @throws ReflectionException
     */
    public function update(Request $request)
    {
        $bank_data    = (new BankInformation($request->all()))->noNullableArray();
        $partner_data = [
            'bkash_no'           => !empty($request->bkash_no) ? formatMobile($request->bkash_no) : null,
            'bkash_account_type' => $request->bkash_account_type
        ];
        if ($this->bank_information) {
            $this->bank_information->update($this->withBothModificationFields($bank_data));
        } else {
            $bank_data['partner_id'] = $this->partner->id;
            PartnerBankInformation::create($this->withCreateModificationField($bank_data));
        }
        $this->partner->update($this->withBothModificationFields($partner_data));

    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function completion()
    {
        $data = $this->toArray();
        return (new Completion($data, [
            $this->profile->updated_at,
            $this->partner->updated_at,
            $this->bank_information ? $this->bank_information->updated_at : null
        ], [
            'routing_no',
            'debit_sum',
            'credit_sum',
            'monthly_avg_credit_sum',
            'disbursement_amount',
            'period'
        ]))->get();
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function toArray()
    {
        return $this->loanDetails ? $this->getDataFromLoanRequest() : $this->getDataFromProfile();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getDataFromLoanRequest()
    {
        $data = $this->loanDetails->getData();
        if (isset($data['finance'])) {

            $data = $data['finance'];
        } elseif (($data = $data[0]) && isset($data['finance_info'])) {
            $data = $data['finance_info'];
        } else {
            $data = [];
        }
        $output = [];
        $output = (new BankInformation($data))->toArray();
        if (array_key_exists('bkash', $data)) {
            $output['bkash'] = [
                'bkash_no'            => array_key_exists('bkash_no', $data['bkash']) ? $data['bkash']['bkash_no'] : null,
                'bkash_account_type'  => array_key_exists('bkash_account_type', $data['bkash']) ? $data['bkash']['bkash_account_type'] : null,
                'bkash_account_types' => constants('BKASH_ACCOUNT_TYPE'),
            ];
        } else {
            $output['bkash'] = [
                'bkash_no'            => null,
                'bkash_account_type'  => null,
                'bkash_account_types' => constants('BKASH_ACCOUNT_TYPE'),
            ];
        }
        $output['acc_types'] = constants('BANK_ACCOUNT_TYPE');
        return $output;
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getDataFromProfile()
    {
        return array_merge((new BankInformation(($this->bank_information ? $this->bank_information->toArray() : [])))->toArray(), [
            'acc_types' => constants('BANK_ACCOUNT_TYPE'),
            'bkash'     => [
                'bkash_no'            => $this->partner->bkash_no,
                'bkash_account_type'  => $this->partner->bkash_account_type,
                'bkash_account_types' => constants('BKASH_ACCOUNT_TYPE')
            ]
        ]);
    }
}
