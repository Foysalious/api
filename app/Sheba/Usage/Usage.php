<?php

namespace Sheba\Usage;

use App\Models\PartnerUsageHistory;
use Sheba\ModificationFields;

class Usage
{
    use ModificationFields;

    public static function Partner()
    {
        return new Partner();
    }

    public function create($data, $modifier = null)
    {
        if (empty($data['partner_id']) && empty($data['type']))
            return 0;
        if (!empty($modifier))
            $this->setModifier($modifier);
        return PartnerUsageHistory::create($this->withCreateModificationField($data));
    }
}
