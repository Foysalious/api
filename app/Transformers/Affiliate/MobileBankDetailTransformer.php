<?php namespace App\Transformers\Affiliate;

use App\Models\ProfileMobileBankInformation;
use League\Fractal\TransformerAbstract;

class MobileBankDetailTransformer extends TransformerAbstract
{
    public function transform(ProfileMobileBankInformation $bank)
    {
        $this->bank = $bank;

        $bank_info = [
            'bank_name' => $bank->bank_name,
            'account_no' => $bank->account_no,
        ];

        return $bank_info;
    }
}
