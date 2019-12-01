<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\PartnerBankInformation;
use App\Models\Resource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class FinanceInfo implements Arrayable
{
    use ModificationFields;
    private $partner;
    private $resource;
    private $loanRequest;
    private $profile;
    private $bank_information;

    public function __construct(Partner $partner, Resource $resource, PartnerLoanRequest $loanRequest = null)
    {
        $this->partner          = $partner;
        $this->resource         = $resource;
        $this->profile          = $resource->profile;
        $this->loanRequest      = $loanRequest;
        $this->bank_information = (new BankInformation($partner->bankInformations))->toArray();
    }

    public static function getValidators()
    {
        return [
            'acc_name'           => 'required|string',
            'acc_no'             => 'required|string',
            'bank_name'          => 'required|string',
            'branch_name'        => 'required|string',
            'acc_type'           => 'string|in:savings,current',
            'bkash_no'           => 'string|mobile:bd',
            'bkash_account_type' => 'string|in:personal,agent,merchant'
        ];
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->loanRequest ? $this->getDataFromLoanRequest() : $this->getDataFromProfile();
    }

    private function getDataFromLoanRequest()
    {
        return [];
    }

    private function getDataFromProfile()
    {
        return array_merge($this->bank_information, [
            'acc_types' => constants('BANK_ACCOUNT_TYPE'),
            'bkash'     => [
                'bkash_no'            => $this->partner->bkash_no,
                'bkash_account_type'  => $this->partner->bkash_account_type,
                'bkash_account_types' => constants('BKASH_ACCOUNT_TYPE')
            ]
        ]);
    }

    public function update(Request $request)
    {
        $bank_data    = (new BankInformation($request))->toArray();
        $partner_data = [
            'bkash_no'           => !empty($request->bkash_no) ? formatMobile($request->bkash_no) : null,
            'bkash_account_type' => $request->bkash_account_type
        ];
        if ($this->partner->bankInformations) {
            $this->partner->bankInformations->update($this->withBothModificationFields($bank_data));
        } else {
            PartnerBankInformation::create($this->withCreateModificationField($bank_data));
        }
        $this->partner->update($this->withBothModificationFields($partner_data));
        return api_response($request, 1, 200);

    }
}
