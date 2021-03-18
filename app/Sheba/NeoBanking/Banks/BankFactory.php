<?php namespace Sheba\NeoBanking\Banks;

use App\Models\Partner;
use Sheba\Dal\NeoBank\Model as NeoBank;

use Sheba\NeoBanking\Exceptions\InvalidBankCode;
use Sheba\NeoBanking\Statics\BankStatics;

class BankFactory
{
    /** @var NeoBank $bank */
    private $bank;
    /** @var Partner $partner */
    private $partner;
    private $mobile;

    /**
     * @param NeoBank $bank
     * @return BankFactory
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }

    /**
     * @param Partner $partner
     * @return BankFactory
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @param NeoBank $neoBank
     * @param Partner $partner
     * @return Bank
     * @throws InvalidBankCode
     */
    public  function get()
    {
        $classMap      = BankStatics::classMap();
        $bankClassPath = "Sheba\\NeoBanking\\Banks\\";
        $code          = $this->bank->bank_code;
        if (isset($classMap[$code])) {
            $class=$classMap[$code];
            /** @var Bank $bank */
            $bank = app("$bankClassPath$class\\$class");
            $bank->setBank($this->bank)->setMobile($this->mobile)->setPartner($this->partner);
            return $bank;
        }
        throw new InvalidBankCode();
    }
}
