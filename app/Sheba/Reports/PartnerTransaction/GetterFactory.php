<?php namespace Sheba\Reports\PartnerTransaction;

use Illuminate\Http\Request;
use Sheba\Reports\PartnerTransaction\Getters\DateRange;
use Sheba\Reports\PartnerTransaction\Getters\Lifetime;

class GetterFactory
{
    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request)
    {
        if ($request->timeline_type_on_partner_transaction == 'lifetime' || $request->timeline_type_on_partner_transaction == null) {
            $getter = app(Lifetime::class);
        } else {
            $getter = app(DateRange::class);
        }

        /** Getter $getter */
        return $getter->get($request);
    }
}