<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\PartnerBankInformation;
use App\Models\Resource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Sheba\Loan\Completion;
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
        $this->bank_information = $partner->bankInformations;
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
     * @throws \ReflectionException
     */
    public function toArray()
    {
        return $this->loanRequest ? $this->getDataFromLoanRequest() : $this->getDataFromProfile();
    }

    private function getDataFromLoanRequest()
    {
        return [];
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getDataFromProfile()
    {
        return array_merge((new BankInformation($this->bank_information->toArray()))->toArray(), [
            'acc_types' => constants('BANK_ACCOUNT_TYPE'),
            'bkash'     => [
                'bkash_no'            => $this->partner->bkash_no,
                'bkash_account_type'  => $this->partner->bkash_account_type,
                'bkash_account_types' => constants('BKASH_ACCOUNT_TYPE')
            ]
        ]);
    }

    /**
     * @param Request $request
     * @throws \ReflectionException
     */
    public function update(Request $request)
    {
        $bank_data    = (new BankInformation($request))->toArray();
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
     * @throws \ReflectionException
     */
    public function completion()
    {
        $data = $this->toArray();
        return (new Completion($data, [
            $this->profile->updated_at,
            $this->partner->updated_at,
            $this->bank_information->updated_at
        ]))->get();
    }
}
