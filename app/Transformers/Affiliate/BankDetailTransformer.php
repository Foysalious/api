<?php namespace App\Transformers\Affiliate;

use App\Models\ProfileBankInformation;
use League\Fractal\TransformerAbstract;

class BankDetailTransformer extends TransformerAbstract
{
    public function transform(ProfileBankInformation $bank)
    {
        $this->bank = $bank;

        $bank_info = [
            'bank_name' => $bank->bank_name,
            'branch_name' => $bank->branch_name,
            'account_no' => $bank->account_no,

        ];

        return $bank_info;
    }
}
