<?php


namespace Sheba\NeoBanking\Repositories;

use Sheba\Dal\NeoBank\Model as NeoBank;

class NeoBankRepository extends NeoBank
{
    public static function getPrimeBank()
    {
        return self::where('name', 'LIKE', '%Prime Bank%')->first();
    }

}
