<?php


namespace Sheba\NeoBanking\Banks;


use App\Models\Partner;
use Illuminate\Contracts\Support\Arrayable;
use ReflectionException;

class BankHomeInfo implements Arrayable
{
    /** @var Bank $bank */
    private $bank;
    /** @var Partner $partner */
    private $partner;

    /**
     * @param Bank $bank
     * @return BankHomeInfo
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $accountInfo = $this->bank->accountInfo();

        return array_merge([
            'bank_id'   => $this->bank->id,
            'bank_name' => ['en' => $this->bank->name, 'bn' => $this->bank->name_bn],
            'logo'      => $this->bank->logo,
            'code'      => $this->bank->code
        ], $accountInfo);
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }
}
