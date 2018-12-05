<?php namespace Sheba\TopUp\Commission;


class CommissionFactory
{
    /**
     * @return Affiliate|Customer|Partner
     */
    public function getAgentCommission($agent)
    {
        if ($agent instanceof \App\Models\Customer) {
            return new Customer();
        } elseif ($agent instanceof \App\Models\Partner) {
            return new Partner();
        } else {
            return new Affiliate();
        }
    }
}