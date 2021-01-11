<?php

use Factories\Factory;
use Factories\CategoryFactory;
use Factories\ServiceFactory;

$factory_classes = [
    CategoryFactory::class,
    ServiceFactory::class
];

foreach ($factory_classes as $factory_class) {
    /** @var Factory $f */
    $f = (new $factory_class($factory));
    $f->handle();
}


/*
 *
$factory->define(Location::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds, [
        'city_id' => 1,
        'name' => $faker->city,
        'geo_informations' => '{"lat":23.75655,"lng":90.387215,"radius":"1.1","geometry":{"type":"Polygon","coordinates":[[[90.3898,23.75835],[90.38458,23.75791],[90.38449,23.75685],[90.38445,23.75499],[90.3855,23.75495],[90.38664,23.755],[90.38877,23.75475],[90.38967,23.7566],[90.38998,23.758],[90.3898,23.75835],[90.3898,23.75835]]]},"center":{"lat":23.75655,"lng":90.387215}}',
        'publication_status' => 1,
        'is_published_for_partner' => 1,

    ]);
});

$factory->define(Profile::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds, [
        'name' => $faker->name,
        'mobile' =>'+8801678242955',
        'email' =>'tisha@sheba.xyz',
        'password' =>bcrypt('12345'),
        'is_blacklisted'=> 0,
        'mobile_verified'=>1,
        'email_verified'=>1,
        'nid_verification_request_count'=>0,
        'blood_group'=>'B+'
    ]);
});

$factory->define(Affiliate::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds, [
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
});

$factory->define(AuthorizationRequest::class, function (Faker\Generator $faker) use ($common_seeds) {
    return [
        'purpose' => 'login',
        'status' => 'success',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
    ];
});

$factory->define(AuthorizationToken::class, function (Faker\Generator $faker) use ($common_seeds) {
    return [
        'valid_till' =>Carbon::now()->addDay() ,
        'refresh_valid_till' =>Carbon::now()->addDays(7) ,
        'is_blacklisted' => 0,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
        'updated_by' => 1,
    ];
});

$factory->define(TopUpVendor::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds,[
        'name' => 'Mock',
        'amount' => '100000',
        'gateway' => 'ssl',
        'sheba_commission' => 4.0,
        'is_published' => 1,
    ]);
});

$factory->define(TopUpVendorCommission::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds,[
        'agent_commission' => '1.00',
        'ambassador_commission' =>'0.20',
        'type' =>'App\Models\Affiliate',
    ]);
});

$factory->define(Sheba\Dal\TopUpOTFSettings\Model::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds,[
        'applicable_gateways'=>'["ssl","airtel"]',
        'type'=>'App\Models\Affiliate',
        'agent_commission'=>'5.03',
    ]);
});
$factory->define(Sheba\Dal\TopUpVendorOTF\Model::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds,[
        'amount' =>'104' ,
        'name_en' =>'jkfhik' ,
        'name_bn' => 'hurefi',
        'description' =>'fgeywgw',
        'type' =>'Bundle',
        'sim_type' =>'Prepaid' ,
        'cashback_amount' =>'12.00' ,
        'status' =>'Active',
    ]);
});

$factory->define(Sheba\Dal\TopUpVendorOTFChangeLog\Model::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds,[
        'from_status'=>'Deactive',
        'to_status'=>'Active',
        'log'=>'OTF status changed from Deactive to Active.',
    ]);
});

$factory->define(Customer::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds,[
        'remember_token'=>$faker->randomLetter,
        'wallet'=>'10000',
        'reward_point'=>'5000',
        'order_count'=>'0',
        'served_order_count'=>'0',
        'voucher_order_count'=>'0',
    ]);
});

$factory->define(Resource::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds,[
        'father_name'=>$faker->name,
        'remember_token'=>$faker->randomLetter,
        'status'=>'Verified',
        'is_verified'=>1,
        'wallet'=>'10000',
        'reward_point'=>'0',

    ]);

});

$factory->define(Member::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds,[
        'remember_token'=>$faker->randomLetter,
        'is_verified'=>1,
    ]);

});
*/
