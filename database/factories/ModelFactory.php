<?php

use App\Models\Affiliate;
use App\Models\Location;
use App\Models\Profile;
use App\Models\TopUpVendor;
use Carbon\Carbon;
use Sheba\Dal\AuthorizationRequest\AuthorizationRequest;
use Sheba\Dal\AuthorizationToken\AuthorizationToken;
use Sheba\Dal\Category\Category;
use Sheba\Dal\Service\Service;

$common_seeds = [
    'created_by' => 1,
    'created_by_name' => 'IT - Shafiqul Islam',
    'updated_by' => 1,
    'updated_by_name' => 'IT - Shafiqul Islam',
    'created_at' => Carbon::now(),
    'updated_at' => Carbon::now()
];

$factory->define(Category::class, function (Faker\Generator $faker) use ($common_seeds) {
    return array_merge($common_seeds, [
        'name' => "Category #" . $faker->randomNumber(),
        'slug' => $faker->slug,
        'short_description' => $faker->text,
        'long_description' => $faker->paragraph,
        'thumb' => $faker->imageUrl(),
        'app_thumb' => $faker->imageUrl(),
        'banner' => $faker->imageUrl(),
        'app_banner' => $faker->imageUrl(),
        'video_link' => $faker->url
    ]);
});

$factory->define(Service::class, function (Faker\Generator $faker) use ($common_seeds) {
    $faqs = [];
    for ($i = 1; $i <= $faker->numberBetween($min = 1, $max = 5); $i++) {
        $question = [
            'question' => rtrim($faker->sentence, '.') . "?",
            'answer' => $faker->paragraph
        ];
        $faqs[] = $question;
    }

    return array_merge($common_seeds, [
        'name' => "Service #" . $faker->randomNumber(),
        'slug' => $faker->slug,
        'description' => $faker->paragraph,
        'app_thumb' => $faker->imageUrl(),
        'app_banner' => $faker->imageUrl(),
        'faqs' => json_encode($faqs),
    ]);
});

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
        'blood_group'=>'B+',
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
