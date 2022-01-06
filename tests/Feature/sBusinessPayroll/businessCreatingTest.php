<?php

namespace Tests\Feature\sBusinessPayroll;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use Faker\Factory;
use Tests\Feature\FeatureTestCase;

/**
 * @author Nawshin Tabassum <nawshin.tabassum@sheba.xyz>
 */
class businessCreatingTest extends FeatureTestCase
{
    private $businessMember;
    private $join_date = '2021-09-28';

    public function testCreatingBusiness()
    {
        $this->truncateTables([
            Profile::class,
            Business::class,
            Member::class,
            BusinessMember::class,
        ]);

        $faker = Factory::create();

        $this->business = Business::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->profile = Profile::factory()->create([
                'email'  => $faker->firstname().'@gmail.com',
                'mobile' => '0181234567'.$i,
            ]);

            $this->member = Member::factory()->create([
                'profile_id'     => $this->profile->id,
                'remember_token' => $faker->firstName(),
            ]);

            $this->businessMember = BusinessMember::factory()->create([
                'business_id' => $this->business->id,
                'member_id'   => $this->member->id,
                'join_date'   => $this->join_date,
            ]);
        }
    }
}


