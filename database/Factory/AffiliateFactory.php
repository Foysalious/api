<?php namespace Factory;


use App\Models\Affiliate;

class AffiliateFactory extends Factory
{

    protected function getModelClass()
    {
       return Affiliate::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'is_ambassador'=>0,
            'is_moderator'=>0,
            'acquisition_cost'=>100,
            'wallet'=>10000,
            'robi_topup_wallet'=>100000,
            'total_earning'=>0,
            'total_gifted_amount'=>0,
            'total_gifted_number'=>0,
            'is_banking_info_verified'=>1,
            'reject_reason'=>'',
            'is_suspended'=>0,
            'remember_token'=>str_random(50),
            'verification_status'=>'verified'
        ]);
    }
}