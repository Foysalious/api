<?php


namespace Sheba\NeoBanking\Repositories;

use App\Models\Partner;
use Sheba\Dal\NeoBank\Model as NeoBank;

class NeoBankRepository extends NeoBank
{
    /** @var Partner $partner */
    private $partner;

    /**
     * @param Partner $partner
     * @return NeoBankRepository
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public static function getPrimeBank()
    {
        return self::where('bank_code', 'NEO_1')->first();
    }

    public function getAll()
    {
        return self::all();
    }

    public function getByCode($code)
    {
        return self::where('bank_code', $code)->first();
    }

}
