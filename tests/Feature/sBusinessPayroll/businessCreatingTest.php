<?php namespace Feature\sBusinessPayroll;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\FeatureTestCase;

class businessCreatingTest extends FeatureTestCase
{

    private $businessMember;
//    protected $profile = 0;
    private $join_date = '2021-09-28';

    public function testCreatingBusiness()
    {
        $this->truncateTables([
            Profile::class,
            Business::class,
            Member::class,
            BusinessMember::class
        ]);

        $faker = \Faker\Factory::create();

        $this->business = factory(Business::class)->create();

        for ($i = 0; $i < 5; $i++){

            $this->profile = factory(Profile::class)->create([
                'email'=> $faker->firstname() . '@gmail.com',
                'mobile' => '0181234567' . $i
            ]);

            $this->member = factory(Member::class)->create([
                'profile_id' => $this->profile->id,
                'remember_token' => $faker->firstName()
            ]);

            $this->businessMember = factory(BusinessMember::class)->create([
                'business_id' => $this->business->id,
                'member_id' => $this->member->id,
                'join_date' => $this->join_date
            ]);
        }
    }

}


